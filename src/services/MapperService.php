<?php

namespace statikbe\deepl\services;

use craft\base\Component;
use craft\elements\Entry;
use DeepL\Translator;

class MapperService extends Component
{

    public function init(): void
    {

    }

    public function entryMapper(Entry $entry) {

        $entry->getFieldLayout();
        if ($entryType->fieldLayoutId) {
            $typeFields = Craft::$app->fields->getFieldsByLayoutId($entryType->getFieldLayoutId());
        }
        $entry = new Entry([
            'sectionId' => (int)$section->id,
            'siteId' => $siteId ? $siteId : Craft::$app->getSites()->getPrimarySite()->id,
            'typeId' => $entryType->id,
            'title' => Seeder::$plugin->fields->Title(),
        ]);
        Craft::$app->getElements()->saveElement($entry);
        Seeder::$plugin->seeder->saveSeededEntry($entry);
        if ($entryType->fieldLayoutId) {
            $entry = Seeder::$plugin->seeder->populateFields($typeFields, $entry);
            $entry->updateTitle();
            $entry->slug = '';
            Craft::$app->getElements()->saveElement($entry);
        }
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
                if (Seeder::$plugin->getSettings()->debug) {
                    throw new FieldNotFoundException('Fieldtype not supported: ' . $fieldType);
                } else {
                    Craft::warning("Fieldtype not supported: $fieldType", __CLASS__);
                }
            }
        } else {
//            if (Seeder::$plugin->getSettings()->debug) {
//                throw new FieldNotFoundException('Fieldtype not supported: ' . $fieldType);
//            } else {
//                Craft::warning("Fieldtype not supported: $fieldType", __CLASS__);
//            }
        }
    }
}