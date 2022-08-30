<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use DeepL\Translator;

class ApiService extends Component
{
    public Translator $translator;

    public function init(): void
    {
        $authKey = getenv('DEEPL_KEY');
        $this->translator = new Translator($authKey);
    }

    public function translateString($text, $sourceLang, $targetLang)
    {
        $translation = $this->translator->translateText(
            $text,
            $this->getLanguageString($sourceLang, false),
            $this->getLanguageString($targetLang, true)
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