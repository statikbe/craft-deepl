<?php

namespace statikbe\deepl\controllers;

use Craft;
use craft\base\Element;
use craft\behaviors\DraftBehavior;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\web\Controller;
use statikbe\deepl\Deepl;
use yii\base\Exception;

class TranslationController extends Controller
{
    public function actionIndex()
    {
        // TODO: run check to see if we have an API key
        try {
            $entryId = Craft::$app->getRequest()->getRequiredQueryParam('entry');
            $sourceSiteId = Craft::$app->getRequest()->getRequiredQueryParam('sourceLocale');
            $destinationSiteId = Craft::$app->getRequest()->getRequiredQueryParam('destinationLocale');

            $sourceSite = Craft::$app->getSites()->getSiteById($sourceSiteId);
            $destinationSite = Craft::$app->getSites()->getSiteById($destinationSiteId);


            $sourceEntry = Entry::findOne(['id' => $entryId, 'siteId' => $sourceSiteId, 'status' => null]);
            $targetEntry = Entry::findOne(['id' => $entryId, 'siteId' => $destinationSiteId, 'status' => null]);

            //TODO Handle different section propagation methods ?

            $newTitle = Deepl::getInstance()->api->translateString(
                $sourceEntry->title,
                $sourceSite->language,
                $destinationSite->language
            );
            $targetEntry->title = $newTitle;

            $targetEntry->slug = "";

            $newValues = Deepl::getInstance()->mapper->entryMapper($sourceEntry, $targetEntry);

            // Save the translated version of the entry as a new draft
            /** @var Element|DraftBehavior $element */
            $draft = Craft::$app->getDrafts()->createDraft(
                $targetEntry,
                Craft::$app->getUser()->getIdentity()->id,
                'Translation',
                'Creating DeepL translation',
            );
            $draft->setCanonical($targetEntry);
            $draft->setScenario(Element::SCENARIO_ESSENTIALS);
            $draft->setFieldValues($newValues);
        } catch (Exception $e) {
            $this->returnError($sourceEntry);
        }

        if (!$draft->validate()) {
            $this->returnError($sourceEntry);
        }

        Craft::$app->getElements()->saveElement($draft);

        return $this->asSuccess("Translation saved as draft", [], $draft->getCpEditUrl(), [
            'details' => !$draft->dateDeleted ? Cp::elementHtml($draft) : null,
        ]);
    }

    private function returnError(Entry $entry)
    {
        return $this->asFailure("Couldn't create translation", [], [
            'details' => !$entry->dateDeleted ? Cp::elementHtml($entry) : null,
        ]);
    }
}
