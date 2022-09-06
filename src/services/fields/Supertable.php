<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\models\Site;
use statikbe\deepl\Deepl;
use verbb\supertable\elements\SuperTableBlockElement;
use verbb\supertable\fields\SuperTableField;


class Supertable extends Component
{

    public function SuperTableField(SuperTableField $field, Element $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        // Handle different types of propagation methods here

        $blocks = $sourceEntry->getFieldValue($field->handle)->all();
        $data = [];
        /** @var SuperTableBlockElement $block */
        foreach ($blocks as $key => $block) {
            $blockType = $block->type;
            $data[$key]['type'] = $blockType->id;
            $data[$key]['enabled'] = true;
            $blockFields = $block->getFieldLayout()->getCustomFields();
            foreach ($blockFields as $blockField) {
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
            }
        }

        return $data;

        if ($field->propagationMethod === $field::PROPAGATION_METHOD_NONE) {

        }
    }


}