<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\models\Site;
use rias\positionfieldtype\fields\Position;

class positionfieldtype extends Component
{
    public function Position(Position $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): string|bool|null
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        return $content;
    }
}
