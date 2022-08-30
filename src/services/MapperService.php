<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\elements\Entry;
use craft\errors\FieldNotFoundException;
use DeepL\Translator;
use statikbe\deepl\Deepl;
use Craft;

class MapperService extends Component
{

    public function init(): void
    {

    }

    public function entryMapper(Entry $sourceEntry, Entry $targetEntry)
    {
        $sourceSite = Craft::$app->getSites()->getSiteById($sourceEntry->siteId);
        $targetSite = Craft::$app->getSites()->getSiteById($targetEntry->siteId);


        $fieldLayoutId = $targetEntry->getFieldLayout()->id;
        if (!$fieldLayoutId) {
            // Throw exception
        }

        $layout = Craft::$app->getFields()->getLayoutById($fieldLayoutId);

        foreach ($layout->getCustomFields() as $field) {
            try {
                $fieldData = $this->isFieldSupported($field);
                if ($fieldData) {
                    $fieldProvider = $fieldData[0];
                    $fieldType = $fieldData[1];
                    $translation = Deepl::getInstance()->$fieldProvider->$fieldType(
                        $field,
                        $sourceEntry,
                        $sourceSite,
                        $targetSite
                    );
                    $targetEntry->setFieldValue($field['handle'], $translation);
                }
            } catch (FieldNotFoundException $e) {
                Craft::error("Fieldtype not supported: " . get_class($field), __CLASS__);
            }
        }
        return $targetEntry;
    }


    private function isFieldSupported($field)
    {
        $fieldType = explode('\\', get_class($field));
        $fieldProvider = $fieldType[1];
        $fieldType = end($fieldType);

        if (class_exists('statikbe\\deepl\\services\\fields\\' . $fieldProvider)) {
            if (in_array($fieldType, get_class_methods(Deepl::getInstance()->$fieldProvider))) {
                return [$fieldProvider, $fieldType];
            } else {
                throw new FieldNotFoundException("Field not suppurted");
//                Craft::warning("Fieldtype not supported: $fieldType", __CLASS__);
            }
        } else {
            throw new FieldNotFoundException("Field not suppurted");
//            Craft::warning("Fieldtype not supported: $fieldType", __CLASS__);
        }
    }
}