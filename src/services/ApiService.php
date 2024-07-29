<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\helpers\App;
use DeepL\DeepLException;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\Translator;
use statikbe\deepl\Deepl;
use statikbe\deepl\models\GlossaryModel;

class ApiService extends Component
{
    public Translator $translator;

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

    public function translateString($text, $sourceLang, $targetLang)
    {
        if (empty($text)) {
            return $text;
        }

        $options = ["tag_handling" => "xml"];

        $glossary = Deepl::getInstance()->glossary->getGlossaryForLanguages($sourceLang, $targetLang);

        if ($glossary) {
            $options['glossary'] = $glossary;
        }

        $translation = $this->translator->translateText(
            $text,
            $this->getLanguageString($sourceLang, false),
            $this->getLanguageString($targetLang, true),
            $options
        );

        return $translation->text;
    }

    public function getTargetLanguages()
    {
        return $this->translator->getTargetLanguages();
    }

    public function getSourceLanguages()
    {
        return $this->translator->getSourceLanguages();
    }

    public function getGlossaryPairs()
    {
        return $this->translator->getGlossaryLanguages();
    }

    public function getLanguageString($string, bool $isTarget = true): string
    {
        $str = explode('-', $string);
        $lang = $str[0];

        // TODO: Better handling for support languages
        if ($lang === 'en') {
            if ($isTarget) {
                return 'en-GB';
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
}
