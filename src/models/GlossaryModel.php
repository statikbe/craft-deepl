<?php

namespace statikbe\deepl\models;

use craft\base\Model;

class GlossaryModel extends Model
{
    public string $name = '';

    public string $source = '';

    public string $target = '';

    public array $entries = [];

    public $uid;

    public function populate(array $data)
    {
        $sites = \Craft::$app->getSites();


        $this->source = $data['source'];
        $this->target  = $data['target'];

        $this->name = $data['name'];

        $this->entries = $data['entries'];

    }

    public function apiData(): array
    {
        return [
            'name' => $this->name,
            'source_lang' => $this->source_lang,
            'target_lang' => $this->target_lang,
            'entries' => $this->entries,
            'entries_format' => 'csv'
        ];
    }

    private function parseLanguage(string $language): string
    {
        return $language;
    }
}