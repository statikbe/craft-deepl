<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\helpers\Json;
use craft\models\Site;
use statikbe\configvaluesfield\fields\ConfigValuesFieldField;

class configvaluesfield extends Component
{
    public function ConfigValuesFieldField(ConfigValuesFieldField $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): array|bool|string
    {
        if ($field->type === 'dropdown') {
            return $sourceEntry->getFieldValue($field->handle);
        } else {
            $content = $sourceEntry->getFieldValue($field->handle);
            return Json::decodeIfJson($content);
        }
    }
}
