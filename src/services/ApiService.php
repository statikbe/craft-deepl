<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\helpers\App;
use DeepL\Translator;
use statikbe\deepl\Deepl;

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

    public function translateString($text, $sourceLang, $targetLang)
    {
        if(empty($text)) {
            return $text;
        }

        $translation = $this->translator->translateText(
            $text,
            $this->getLanguageString($sourceLang, false),
            $this->getLanguageString($targetLang, true),
            ["tag_handling" => "xml"]
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
}
