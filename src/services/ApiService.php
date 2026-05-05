<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\helpers\App;
use DeepL\DeepLException;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\TranslateTextOptions;
use DeepL\Translator;
use statikbe\deepl\Deepl;
use statikbe\deepl\events\ModifyTranslateOptionsEvent;
use statikbe\deepl\models\GlossaryModel;

class ApiService extends Component
{
    public const EVENT_BEFORE_TRANSLATE = 'beforeTranslate';

    public Translator $translator;

    private bool $batchMode = false;
    private array $batchQueue = [];
    private int $batchCounter = 0;

    public function init(): void
    {
        $authKey = App::parseEnv(Deepl::getInstance()->getSettings()->apiKey);
        if (!$authKey) {
            // Expand this to show a message and/or throw an exception if appropriate.
            return;
        }
        $this->translator = new Translator($authKey);
    }

    public function createGlossary(GlossaryModel $model): GlossaryInfo
    {
        $entries = GlossaryEntries::fromEntries($model['entries']);
        return $this->translator->createGlossary(
            $model->name,
            $this->parseLanguage($model->source),
            $this->parseLanguage($model->target),
            $entries
        );
    }

    public function getAllGlossaries()
    {
        try {
            return $this->translator->listGlossaries();
        } catch (DeeplException $e) {
            \Craft::error($e->getMessage(), 'deepl');
        }
    }

    public function deleteGlossary($id)
    {
        try {
            $this->translator->deleteGlossary($id);
        } catch (DeepLException $e) {
            \Craft::error($e->getMessage(), 'deepl');
        }
    }

    /**
     * Translates the $string from the $sourceLang to the $targetLang, but only if $translate is set to true
     *  (translate won't be true if the user clicked the copy button instead of the translate button, in which case we simply return $string)
     * @param string $text
     * @param string $sourceLang
     * @param string $targetLang
     * @param bool $translate
     * @return string
     * @throws DeepLException
     */
    public function translateString(string $text, string $sourceLang, string $targetLang, bool $translate = true): string
    {
        if (!$translate || empty($text)) {
            return $text;
        }

        if ($this->batchMode) {
            $placeholder = '__DEEPL_' . $this->batchCounter . '__';
            $token = 'XQZJMP';
            $encodedText = str_replace('&amp;', '<span translate="no">' . $token . '</span>', $text);
            $encodedText = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;)/', '<span translate="no">' . $token . '</span>', $encodedText);

            $this->batchQueue[] = [
                'encoded' => $encodedText,
                'placeholder' => $placeholder,
            ];
            $this->batchCounter++;
            return $placeholder;
        }

        $options = ["tag_handling" => "html"];

        $glossary = Deepl::getInstance()->glossary->getGlossaryForLanguages($sourceLang, $targetLang);

        if ($glossary) {
            $options['glossary'] = $glossary;
        }

// Replace &amp; with a unique gibberish token before sending
        $token = 'XQZJMP'; // meaningless to any language
        $encodedText = str_replace('&amp;', '<span translate="no">' . $token . '</span>', $text);
        $encodedText = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;)/', '<span translate="no">' . $token . '</span>', $encodedText);

        $options = $this->triggerBeforeTranslate($sourceLang, $targetLang, $encodedText, $options);

        $translation = $this->translator->translateText(
            $encodedText,
            $this->getLanguageString($sourceLang, false),
            $this->getLanguageString($targetLang, true),
            $options
        );

        $result = $translation->text;

// Restore token with proper spacing
        $result = preg_replace('/\s*<span translate="no">XQZJMP<\/span>\s*/', ' & ', $result);

// Decode HTML entities DeepL adds (&#x27; = apostrophe etc.)
        $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $result = trim($result);

        return $result;
    }

    public function startBatch(): void
    {
        $this->batchMode = true;
        $this->batchQueue = [];
        $this->batchCounter = 0;
    }

    public function isBatchMode(): bool
    {
        return $this->batchMode;
    }

    public function pauseBatch(): void
    {
        $this->batchMode = false;
    }

    public function resumeBatch(): void
    {
        $this->batchMode = true;
    }

    public function resetBatch(): void
    {
        $this->batchMode = false;
        $this->batchQueue = [];
        $this->batchCounter = 0;
    }

    /**
     * Sends all queued translation strings to DeepL in batched API calls (max 50 per call)
     * and returns a map of placeholder tokens to translated strings.
     */
    public function flushBatch(string $sourceLang, string $targetLang): array
    {
        $this->batchMode = false;

        if (empty($this->batchQueue)) {
            return [];
        }

        $options = ["tag_handling" => "html"];
        $glossary = Deepl::getInstance()->glossary->getGlossaryForLanguages($sourceLang, $targetLang);
        if ($glossary) {
            $options['glossary'] = $glossary;
        }

        $map = [];
        $chunks = array_chunk($this->batchQueue, 50);

        foreach ($chunks as $chunk) {
            $texts = array_column($chunk, 'encoded');

            $chunkOptions = $this->triggerBeforeTranslate($sourceLang, $targetLang, $texts, $options);

            $results = $this->translator->translateText(
                $texts,
                $this->getLanguageString($sourceLang, false),
                $this->getLanguageString($targetLang, true),
                $chunkOptions
            );

            foreach ($chunk as $i => $item) {
                $result = $results[$i]->text;
                $result = preg_replace('/\s*<span translate="no">XQZJMP<\/span>\s*/', ' & ', $result);
                $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $result = trim($result);
                $map[$item['placeholder']] = $result;
            }
        }

        $this->batchQueue = [];
        return $map;
    }

    /**
     * Recursively replaces placeholder tokens in a nested array/string structure
     * with their actual translations from the provided map.
     */
    public function resolveTranslations(mixed $values, array $map): mixed
    {
        if (is_string($values)) {
            if (isset($map[$values])) {
                return $map[$values];
            }
            return strtr($values, $map);
        }
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = $this->resolveTranslations($value, $map);
            }
        }
        return $values;
    }

    public function getTargetLanguages()
    {
        return $this->translator->getTargetLanguages();
    }

    public function getSourceLanguages()
    {
        return $this->translator->getSourceLanguages();
    }

    public function getLanguageString($string, bool $isTarget = true): string
    {
        $str = explode('-', $string);
        $lang = $str[0];

        // TODO: Better handling for support languages
        if ($lang === 'en') {
            if ($isTarget) {
                $str = strtoupper($string);
                if (in_array($str, ['EN', 'EN-US', 'EN-GB'])) {
                    return $str;
                }
                return 'EN-GB';
            } else {
                return "EN";
            }
        }

        return strtoupper($lang);
    }

    private function parseLanguage(string $language): string
    {
        $data = str_split($language, '2');
        return $data[0];
    }

    /**
     * Fires {@see self::EVENT_BEFORE_TRANSLATE} and returns the (possibly
     * mutated) options array to send to DeepL.
     *
     * Listeners may add `custom_instructions`, `context`, `formality`, or any
     * other key supported by `\DeepL\TranslateTextOptions`. Items added to
     * `$event->customInstructions` are merged with any instructions already
     * present in `$event->options[CUSTOM_INSTRUCTIONS]` and capped at 10
     * (DeepL's documented limit).
     *
     * @param string|array<string> $text  The text(s) being translated — context only.
     * @param array<string, mixed> $options  The options array as built so far.
     * @return array<string, mixed>
     */
    private function triggerBeforeTranslate(string $sourceLang, string $targetLang, string|array $text, array $options): array
    {
        $event = new ModifyTranslateOptionsEvent([
            'sourceLang' => $sourceLang,
            'targetLang' => $targetLang,
            'text' => $text,
            'options' => $options,
        ]);

        $this->trigger(self::EVENT_BEFORE_TRANSLATE, $event);

        $merged = $event->options;

        $existing = $merged[TranslateTextOptions::CUSTOM_INSTRUCTIONS] ?? [];
        if (!is_array($existing)) {
            $existing = [$existing];
        }

        $instructions = array_merge(array_values($existing), array_values($event->customInstructions));

        if (!empty($instructions)) {
            $merged[TranslateTextOptions::CUSTOM_INSTRUCTIONS] = array_slice($instructions, 0, 10);
        }

        return $merged;
    }
}
