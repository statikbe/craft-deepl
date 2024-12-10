<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\elements\MatrixBlock;
use craft\errors\InvalidFieldException;
use craft\fields\Matrix;
use craft\fields\PlainText;
use craft\fields\Table;
use craft\models\Site;
use statikbe\deepl\Deepl;

class fields extends Component
{
    /**
     * @param PlainText $field
     * @param Element $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function PlainText(PlainText $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true)
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
            $targetSite->language,
            $translate
        );
    }


    public function Table(Table $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true)
    {
        $data = $sourceEntry->getFieldValue($field->handle);
        $cols = collect($field->columns);

        foreach ($cols->toArray() as $key => $col) {
            $cols[$col['handle']] = $col;
        }

        $newData = [];
        foreach ($data as $key => $row) {
            foreach ($row as $rowKey => $cell) {
                if (in_array($cols[$rowKey]['type'], ['singleline', 'multiline'])) {
                    $newData[$key][$rowKey] = Deepl::getInstance()->api->translateString(
                        $cell,
                        $sourceSite->language,
                        $targetSite->language,
                        $translate
                    );
                } else {
                    $newData[$key][$rowKey] = $cell;
                }
            }
        }

        return $newData;
    }

    /**
     * @param Matrix $field
     * @param Element $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     */
    public function Matrix(Matrix $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true)
    {
        // Handle different types of propagation methods here
        $blocks = $sourceEntry->getFieldValue($field->handle)->all();
        $data = [];
        /** @var MatrixBlock $block */
        foreach ($blocks as $key => $block) {
            $blockType = $block->type;
            $blockFields = $block->getFieldLayout()->getCustomFields();
            if ($block->title) {
                $newTitle = Deepl::getInstance()->api->translateString(
                    $block->title,
                    $sourceSite->language,
                    $targetSite->language,
                    $translate
                );
                $data[$block->id]['title'] = $newTitle;
            }
            foreach ($blockFields as $blockField) {
                try {
                    $fieldData = Deepl::getInstance()->mapper->isFieldSupported($blockField);
                    if ($fieldData) {
                        $fieldProvider = $fieldData[0];
                        $fieldType = $fieldData[1];
                        $translation = Deepl::getInstance()->$fieldProvider->$fieldType(
                            $blockField,
                            $block,
                            $sourceSite,
                            $targetSite,
                            $targetEntry,
                            $translate
                        );
                        $data[$block->id]['fields'][$blockField->handle] = $translation;
                    }
                } catch (InvalidFieldException $e) {
                    $data[$block->id]['fields'][$blockField->handle] = Deepl::getInstance()->mapper->handleUnsupportedField($block, $blockField->handle);
                    \Craft::error("Matrix - Fieldtype not supported: " . get_class($field), __CLASS__);
                }
            }

            if (isset($data[$block->id]) && $data[$block->id] > 0) {
                $data[$block->id]['type'] = $blockType->handle;
                $data[$block->id]['enabled'] = true;
            }
        }
        return $data;
    }
}
