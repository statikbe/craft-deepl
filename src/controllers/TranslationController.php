<?php

namespace statikbe\deepl\controllers;

use craft\elements\Entry;
use craft\web\Controller;
use Craft;
use statikbe\deepl\Deepl;

class TranslationController extends Controller
{
    public function actionIndex()
    {
        $entryId = Craft::$app->getRequest()->getRequiredQueryParam('entry');
        $sourceSiteId = Craft::$app->getRequest()->getRequiredQueryParam('sourceLocale');
        $destinationSiteId = Craft::$app->getRequest()->getRequiredQueryParam('destinationLocale');

        $sourceSite = Craft::$app->getSites()->getSiteById($sourceSiteId);
        $destinationSite = Craft::$app->getSites()->getSiteById($destinationSiteId);


        $sourceEntry = Entry::findOne(['id' => $entryId, 'siteId' => $sourceSiteId, 'status' => null]);
        $result = Deepl::getInstance()->api->translateString(
            $sourceEntry->title,
            Deepl::getInstance()->api->getLanguageString($sourceSite->language),
            Deepl::getInstance()->api->getLanguageString($destinationSite->language)
        );
    }
}