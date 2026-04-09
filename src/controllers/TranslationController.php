<?php

namespace statikbe\deepl\controllers;

use Craft;
use craft\base\Element;
use craft\behaviors\DraftBehavior;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\web\Controller;
use statikbe\deepl\Deepl;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class TranslationController extends Controller
{
    public function actionIndex()
    {
        try {
            $settings = Deepl::getInstance()->getSettings();
            $entryId = Craft::$app->getRequest()->getRequiredQueryParam('entry');
            $sourceSiteId = Craft::$app->getRequest()->getRequiredQueryParam('sourceLocale');
            $destinationSiteId = Craft::$app->getRequest()->getRequiredQueryParam('destinationLocale');

            $translate = (bool) Craft::$app->getRequest()->getQueryParam('translate', 1);

            $sourceSite = Craft::$app->getSites()->getSiteById($sourceSiteId);
            $destinationSite = Craft::$app->getSites()->getSiteById($destinationSiteId);

            $sourceEntry = Entry::findOne(['id' => $entryId, 'siteId' => $sourceSiteId, 'status' => null]);
            $targetEntry = Entry::findOne(['id' => $entryId, 'siteId' => $destinationSiteId, 'status' => null]);

            if (!$sourceEntry || !$targetEntry) {
                return $this->returnError();
            }

            $api = Deepl::getInstance()->api;

            // Start batch mode to collect all strings and translate in one API call
            if ($translate) {
                $api->startBatch();
            }

            if ($sourceEntry->title) {
                $targetEntry->title = $api->translateString(
                    $sourceEntry->title,
                    $sourceSite->language,
                    $destinationSite->language,
                    $translate
                );
            }

            if ($settings->translateSlugs) {
                $targetEntry->slug = "";
            } else {
                $targetEntry->slug = $sourceEntry->slug;
            }

            $newValues = Deepl::getInstance()->mapper->entryMapper($sourceEntry, $targetEntry, $translate);

            // Flush batch: send all collected strings to DeepL in one API call
            if ($translate) {
                $map = $api->flushBatch($sourceSite->language, $destinationSite->language);

                if ($sourceEntry->title && isset($map[$targetEntry->title])) {
                    $targetEntry->title = $map[$targetEntry->title];
                }

                $newValues = $api->resolveTranslations($newValues, $map);
            }

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

            if (!$draft->validate()) {
                return $this->returnError($sourceEntry);
            }

            Craft::$app->getElements()->saveElement($draft);

            return $this->asSuccess("Translation saved as draft", [], $draft->getCpEditUrl(), [
                'details' => !$draft->dateDeleted ? Cp::elementHtml($draft) : null,
            ]);
        } catch (\Throwable $e) {
            \Craft::error($e->getMessage(), 'deepl');
            return $this->returnError($sourceEntry ?? null);
        } finally {
            Deepl::getInstance()->api->resetBatch();
        }
    }

    public function actionAssets()
    {
        // TODO: run check to see if we have an API key
        try {
            $entryId = Craft::$app->getRequest()->getRequiredQueryParam('entry');
            $sourceSiteId = Craft::$app->getRequest()->getRequiredQueryParam('sourceLocale');
            $destinationSiteId = Craft::$app->getRequest()->getRequiredQueryParam('destinationLocale');

            $sourceSite = Craft::$app->getSites()->getSiteById($sourceSiteId);
            $destinationSite = Craft::$app->getSites()->getSiteById($destinationSiteId);


            $sourceEntry = Asset::findOne(['id' => $entryId, 'siteId' => $sourceSiteId, 'status' => null]);
            $targetEntry = Asset::findOne(['id' => $entryId, 'siteId' => $destinationSiteId, 'status' => null]);

            //TODO Handle different section propagation methods ?

            $newTitle = Deepl::getInstance()->api->translateString(
                $sourceEntry->title,
                $sourceSite->language,
                $destinationSite->language
            );
            $targetEntry->title = $newTitle;

            $targetEntry->slug = "";

            $newValues = Deepl::getInstance()->mapper->entryMapper($sourceEntry, $targetEntry);

            $targetEntry->setFieldValues($newValues);
        } catch (Exception $e) {
            $this->returnError($sourceEntry);
        }

        if (!$targetEntry->validate()) {
            $this->returnError($sourceEntry);
        }

        Craft::$app->getElements()->saveElement($targetEntry);

        return $this->asSuccess("Asset automaticly translated - please double check the translation", [], $targetEntry->getCpEditUrl(), [
            'details' => !$targetEntry->dateDeleted ? Cp::elementHtml($targetEntry) : null,
        ]);
    }


    private function returnError(?Entry $entry = null)
    {
        return $this->asFailure("Couldn't create translation", [], [
            'details' => $entry && !$entry->dateDeleted ? Cp::elementHtml($entry) : null,
        ]);
    }
}
