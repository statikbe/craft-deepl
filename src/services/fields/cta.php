<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\models\Site;
use statikbe\cta\fields\CTAField;
use statikbe\deepl\Deepl;

class cta extends Component
{
    public function CTAField(CTAField $field, Element $sourceEntry, Site $sourceSite, Site $targetSite, $translate = true, Element $targetEntry)
    {

        /** @var \statikbe\cta\models\CTA $model */

        $model = $sourceEntry->getFieldValue($field->handle);
        $data = $model->toArray();
        if ($data['customText']) {
            $translation = Deepl::getInstance()->api->translateString(
                $data['customText'],
                $sourceSite->language,
                $targetSite->language,
                $translate
            );
            $data['customText'] = $translation;
        }
        return $data;
    }
}
