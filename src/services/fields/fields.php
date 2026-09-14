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

        if ($this->containsScriptTag($content)) {
            \Craft::info(
                "Skipping field '{$field->handle}': contains a script tag, left untranslated",
                __CLASS__
            );

            return $content;
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle),
            $sourceSite->language,
            $targetSite->language,
            $translate
        );
    }


    /**
     * Markup carrying a script tag - third-party embeds such as Trustpilot, cookie widgets or
     * tracking pixels - is code, not copy. Translating it is pointless and actively harmful: DeepL
     * rewrites text inside attributes, and its HTML parser rejects the whole request on some
     * snippets with "Tag handling parsing failed, please check input. 'text without parent'".
     * Tag handling expects a single root element, while an embed is typically a comment, a script
     * and a div side by side. That failure fails the entire entry, not just this field.
     *
     * Matched on content rather than on the field handle on purpose: these snippets turn up in
     * fields named anything at all (embedContent, reviews, ...), so a naming convention is not
     * something to rely on.
     */
    private function containsScriptTag(mixed $content): bool
    {
        return is_string($content) && preg_match('/<script\b/i', $content) === 1;
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

            if (!empty($data[$block->id])) {
                $data[$block->id]['type'] = $blockType->handle;
                $data[$block->id]['enabled'] = true;
            }
        }
        return $data;
    }
}
