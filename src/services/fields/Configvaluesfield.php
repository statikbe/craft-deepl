<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\helpers\Json;
use craft\models\Site;
use statikbe\configvaluesfield\fields\ConfigValuesFieldField;

class Configvaluesfield extends Component
{

    public function ConfigValuesFieldField(ConfigValuesFieldField $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): array|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        return Json::decode($content);
    }

}