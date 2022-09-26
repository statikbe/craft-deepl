<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\errors\InvalidFieldException;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Matrix;
use craft\fields\PlainText;
use craft\fields\Url;
use craft\models\Site;
use statikbe\deepl\Deepl;


class Fields extends Component
{

    /**
     * @param PlainText $field
     * @param Entry $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function PlainText(PlainText $field, Element $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        if (!$content) {
            return false;
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle),
            $sourceSite->language,
            $targetSite->language
        );
    }

    /**
     * @param Matrix $field
     */
    public function Matrix(Matrix $field, Entry $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        // Handle different types of propagation methods here
        $blocks = $sourceEntry->getFieldValue($field->handle)->all();
        $data = [];
        /** @var MatrixBlock $block */
        foreach ($blocks as $key => $block) {
            $blockType = $block->type;
            $blockFields = $block->getFieldLayout()->getCustomFields();
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
                            $targetSite
                        );
                        $data[$key]['fields'][$blockField->handle] = $translation;
                    }
                }catch (InvalidFieldException $e) {
                    // TODO: if string pass the value, of object log not supported$
                    $data[$key]['fields'][$blockField->handle] = Deepl::getInstance()->mapper->handleUnsupportedField($block, $blockField->handle);
                    \Craft::error("Matrix - Fieldtype not supported: " . get_class($field), __CLASS__);
                }
            }

            if(isset($data[$key])  && $data[$key] > 0) {
                $data[$key]['type'] = $blockType->handle;
                $data[$key]['enabled'] = true;
            }
        }
        return $data;

        if ($field->propagationMethod === $field::PROPAGATION_METHOD_NONE) {

        }
    }


}