<?php

namespace statikbe\deepl\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\errors\InvalidFieldException;
use statikbe\deepl\Deepl;
use yii\log\Logger;

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
        $values = [];
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
                    $values[$field['handle']] = $translation;
                }
            } catch (InvalidFieldException $e) {
                // TODO: if string pass the value, of object log not supported
                $values[$field['handle']] = $this->handleUnsupportedField($sourceEntry, $field->handle);
                Craft::error("Fieldtype not supported: " . get_class($field), __CLASS__);
            }
        }
        return $values;
    }

    public function handleUnsupportedField(Element $element, string $handle)
    {
        if ($element->getFieldValue($handle) instanceof ElementQuery) {
            return $element->getFieldValue($handle)->anyStatus()->ids();
        }
        return $element->getFieldValue($handle);
    }


    public function isFieldSupported($field)
    {
        $fieldType = explode('\\', get_class($field));
        $class = get_class($field);
        $fieldProvider = $fieldType[1];
        $fieldType = end($fieldType);
        try {
            if (class_exists('statikbe\\deepl\\services\\fields\\' . $fieldProvider)) {
                if (in_array($fieldType, get_class_methods(Deepl::getInstance()->$fieldProvider))) {
                    return [$fieldProvider, $fieldType];
                } else {
                    throw new InvalidFieldException(get_class($field), "Field not supported: {$class}");
                }
            } else {
                throw new InvalidFieldException(get_class($field), "Field not supported: {$class}");
            }
        } catch (InvalidFieldException $e) {
            Craft::getLogger()->log($e->getMessage(), Logger::LEVEL_INFO, 'deepl');
            throw $e;
        }
        return false;
    }
}
