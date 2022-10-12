<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\models\Site;
use craft\redactor\Field;
use statikbe\deepl\Deepl;

class Redactor extends Component
{

    public function Field(Field $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): string|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);

        if (!$content) {
            return "";
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle)->getParsedContent(),
            $sourceSite->language,
            $targetSite->language
        );


    }

}