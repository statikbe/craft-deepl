<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\helpers\App;
use DeepL\DeepLException;
use DeepL\Translator;
use statikbe\deepl\Deepl;

class ApiService extends Component
{
    public Translator $translator;

    public function init(): void
    {
        $authKey = App::parseEnv(Deepl::getInstance()->getSettings()->apiKey);
        if (!$authKey) {
            throw new DeepLException('authKey must be a non-empty string');
            return;
        }
        $this->translator = new Translator($authKey);
    }

    public function translateString($text, $sourceLang, $targetLang)
    {
        $translation = $this->translator->translateText(
            $text,
            $this->getLanguageString($sourceLang, false),
            $this->getLanguageString($targetLang, true),
            ["tag_handling" => "xml"]
        );

        return $translation->text;
    }

    public function getLanguageString($string, bool $isTarget = true): string
    {
        if ($isTarget) {
            $supportedLanguages = $this->translator->getTargetLanguages();
        } else {
            $supportedLanguages = $this->translator->getSourceLanguages();
        }

        foreach ($supportedLanguages as $language) {
            if (str_contains($language->code, strtoupper($string))) {
                $lang = $language->code;
                break;
            } else {
                $str = explode('-', $string);
                $shortLang = $str[0];

                if (str_contains(strtolower($language->code), $shortLang)) {
                    $lang = $language->code;
                    break;
                }
            }
        }

        return $lang ?? $string;
    }
}
