<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\models\Site;
use statikbe\deepl\Deepl;
use verbb\hyper\fields\HyperField;
use verbb\hyper\models\LinkCollection;

class hyper extends Component
{
    public function HyperField(HyperField $field, Element $sourceElement, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true)
    {
        /** @var LinkCollection $model */
        $model = $sourceElement->getFieldValue($field->handle);

        $links = $model->getLinks();
        $newLinks = [];
        /** @var \verbb\hyper\base\Link $link */
        foreach ($links as $link) {
            if ($link->linkText) {
                $translation = Deepl::getInstance()->api->translateString(
                    $link->linkText,
                    $sourceSite->language,
                    $targetSite->language,
                    $translate
                );
                $link->linkText = $translation;
            }
            $newLinks[] = $link;
        }
        $model->setLinks($newLinks);

        return $model;
    }
}
