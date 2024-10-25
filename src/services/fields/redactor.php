<?php


namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\models\Site;
use craft\redactor\Field;
use statikbe\deepl\Deepl;

class redactor extends Component
{
    public function Field(Field $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true): string|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);

        if ($field->translationMethod === BaseField::TRANSLATION_METHOD_NONE && $content) {
            return $content;
        }

        if (!$content) {
            return "";
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle)->getParsedContent(),
            $sourceSite->language,
            $targetSite->language,
            $translate
        );
    }
}
