<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\elements\Entry;
use craft\models\Site;
use craft\redactor\Field;
use statikbe\deepl\Deepl;

class Redactor extends Component
{

    public function Field(Field $field, Entry $sourceEntry, Site $sourceSite, Site $targetSite): string|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        if (!$content) {
            return false;
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle)->getParsedContent(),
            $sourceSite->language,
            $targetSite->language
        );


    }

}