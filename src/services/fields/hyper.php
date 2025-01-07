<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\errors\InvalidFieldException;
use craft\models\Site;
use statikbe\deepl\Deepl;
use verbb\hyper\fields\HyperField;
use verbb\hyper\links\Url;
use verbb\hyper\models\LinkCollection;
use verbb\hyper\links\Entry;

class hyper extends Component
{
    public function HyperField(
        HyperField $field,
        Element    $sourceElement,
        Site       $sourceSite,
        Site       $targetSite,
        bool       $translate = true,
        Element    $targetEntry
    )
    {
        /** @var LinkCollection $model */
        $model = $sourceElement->getFieldValue($field->handle);

        $links = $model->getLinks();

        $newLinks = [];
        /** @var \verbb\hyper\base\Link $link */
        foreach ($links as $link) {
            $class = get_class($link);
            $value = \Craft::createObject($class);

            if ($link instanceof Entry) {
                $value->linkSiteId = $targetSite->id;
            }


            $value->linkValue = $link->linkValue;


            $newValues = [];
            foreach ($link->getCustomFields() as $customField) {
                try {
                    $fieldData = Deepl::getInstance()->mapper->isFieldSupported($customField);
                    if ($fieldData) {
                        $fieldProvider = $fieldData[0];
                        $fieldType = $fieldData[1];
                        $translation = Deepl::getInstance()->$fieldProvider->$fieldType(
                            $customField,
                            $link,
                            $sourceSite,
                            $targetSite,
                            $translate,
                            $targetEntry
                        );
                        $newValues[$customField->handle] = $translation;
                    }
                } catch (InvalidFieldException $e) {
                    $newValues[$customField->handle] = Deepl::getInstance()->mapper->handleUnsupportedField($link, $customField->handle);
                    \Craft::error("Hyper - Fieldtype not supported: " . get_class($field), __CLASS__);
                }
            }

            $translation = Deepl::getInstance()->api->translateString(
                $link->linkText,
                $sourceSite->language,
                $targetSite->language,
                $translate
            );

            $value->fields = $newValues;
            $value->setAttributes($link->getAttributes());
            $value->linkText = $translation;

            $newLinks[] = $value;
        }

        return $newLinks;

    }
}