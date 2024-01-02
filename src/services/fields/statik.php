<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\models\Site;
use modules\statik\fields\AnchorLink;
use statikbe\deepl\Deepl;

class statik extends Component
{
    /**
     * @param AnchorLink $field
     * @param Element $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function AnchorLink(AnchorLink $field, Element $sourceEntry, Site $sourceSite, Site $targetSite): string|bool
    {
        $content = $sourceEntry->getFieldValue($field->handle);

        if ($field->translationMethod === BaseField::TRANSLATION_METHOD_NONE && $content) {
            return $content;
        }

        if (!$content) {
            return "";
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle),
            $sourceSite->language,
            $targetSite->language
        );
    }
}
