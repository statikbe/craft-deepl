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

    public function rules(): array
    {
        return [
            [['entries'], 'checkIsArray'],
            [['name', 'source', 'target'], 'string'],
            [['name', 'source', 'target', 'entries'], 'required'],
        ];
    }


    public function populate(array $data): void
    {
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['source'])) {
            $this->source = $data['source'];
        }

        if (isset($data['target'])) {
            $this->target = $data['target'];
        }

        if (isset($data['entries'])) {
            $this->entries = $data['entries'];
        }
    }

    public function checkIsArray($attribute): void
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, "{$attribute} is not an array");
        }
    }
}