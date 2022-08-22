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

        //Handle different section propagation methods ?
        $targetEntry = Entry::findOne(['id' => $entryId, 'siteId' => $destinationSiteId, 'status' => null]);
        $newTitle = Deepl::getInstance()->api->translateString(
            $sourceEntry->title,
            Deepl::getInstance()->api->getLanguageString($sourceSite->language),
            Deepl::getInstance()->api->getLanguageString($destinationSite->language)
        );
        $targetEntry->title = $newTitle;

        // Save the translated version of the entry as a new draft
        Craft::$app->getDrafts()->saveElementAsDraft($targetEntry, Craft::$app->getUser()->getIdentity()->id);

        // Redirect to the translated entry
        return $this->redirect($targetEntry->getCpEditUrl());


    }
}