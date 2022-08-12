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
        return $this->translator->translateText(
            $text,
            $sourceLang,
            $targetLang
        );
    }

    public function getLanguageString($string): string
    {
        $str = explode('-', $string);
        $lang = $str[0];
        if (!in_array($lang, ['en'])) {
            return $lang;
        }

        if ($lang === 'en') {
            return 'en-US';
        }
    }
}