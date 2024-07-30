<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\models\Site;
use nystudio107\seomatic\fields\SeoSettings;
use statikbe\deepl\Deepl;

class seomatic extends Component
{
    /**
     * @param SeoSettings $field
     * @param Element $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function SeoSettings(SeoSettings $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, $translalte = true)
    {
        $metaBundle = $sourceEntry->getFieldValue($field->handle);

        if ($field->translationMethod === BaseField::TRANSLATION_METHOD_NONE && $metaBundle) {
            return $metaBundle;
        }

        if (!$metaBundle) {
            return "";
        }

        $model = $metaBundle->metaGlobalVars;

        $data = $model->toArray();
        foreach ($data as $key => $value) {
            if ($value and !is_array($value)) {
                $translation = Deepl::getInstance()->api->translateString(
                    $value,
                    $sourceSite->language,
                    $targetSite->language,
                    $translate
                );
                $data[$key] = $translation;
            } else {
                $data[$key] = $value;
            }
        }

        $metaBundle->metaGlobalVars = $data;

        return $metaBundle;
    }
}
