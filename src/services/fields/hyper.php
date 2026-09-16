<?php

namespace statikbe\deepl\services\fields;

use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\models\Site;
use statikbe\deepl\Deepl;
use verbb\hyper\fields\HyperField;
use verbb\hyper\models\LinkCollection;
use verbb\hyper\base\ElementLink;

class hyper extends Component
{
    public function HyperField(HyperField $field, Element $sourceElement, Site $sourceSite, Site $targetSite, Element $targetEntry, $translate = true)
    {
        /** @var LinkCollection $model */
        $model = $sourceElement->getFieldValue($field->handle);

        // Hyper returns an object with translated properties set directly,
        // so we bypass batch mode and translate individually here.
        $api = Deepl::getInstance()->api;
        $wasBatching = $api->isBatchMode();
        if ($wasBatching) {
            $api->pauseBatch();
        }

        $links = $model->getLinks();
        $newLinks = [];
        /** @var \verbb\hyper\base\Link $link */
        foreach ($links as $link) {
            if ($link->linkText) {
                $translation = $api->translateString(
                    $link->linkText,
                    $sourceSite->language,
                    $targetSite->language,
                    $translate
                );
                $link->linkText = $translation;
            }
            if ($link instanceof ElementLink) {
                $entryExists = Entry::find()->siteId($targetSite->id)->id($link->linkValue)->status(null)->one();
                if ($entryExists) {
                    $link->linkSiteId = $targetSite->id;
                }
            }
            $newLinks[] = $link;
        }
        $model->setLinks($newLinks);

        if ($wasBatching) {
            $api->resumeBatch();
        }

        return $model;
    }
}
