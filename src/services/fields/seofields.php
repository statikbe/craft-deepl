<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field as BaseField;
use craft\models\Site;
use statikbe\deepl\Deepl;
use studioespresso\seofields\fields\SeoField;


class seofields extends Component
{

    /**
     * @param SeoField $field
     * @param Element $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function SeoField(SeoField $field, Element $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        $model = $sourceEntry->getFieldValue($field->handle);

        if($field->translationMethod === BaseField::TRANSLATION_METHOD_NONE && $model) {
            return $model;
        }

        if (!$model) {
            return "";
        }

        $data = $model->toArray();
        foreach ($data as $key => $value) {
            if($value and !is_array($value)){
                $translation = Deepl::getInstance()->api->translateString(
                    $value,
                    $sourceSite->language,
                    $targetSite->language
                );
                $data[$key] = $translation;
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

}