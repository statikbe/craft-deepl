<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\models\Site;
use statikbe\deepl\Deepl;

class ckeditor extends Component
{
    public function Field(\craft\ckeditor\Field $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): string|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        if ($field->translationMethod === BaseField::TRANSLATION_METHOD_NONE && $content) {
            return $content;
        }

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
