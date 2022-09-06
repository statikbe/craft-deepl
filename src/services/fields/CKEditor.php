<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\models\Site;
use statikbe\deepl\Deepl;

class CKEditor extends Component
{

    public function Field(\craft\ckeditor\Field $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): string|bool
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