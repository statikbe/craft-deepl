<?php

namespace statikbe\deepl\console\controllers;

use craft\console\Controller;
use statikbe\deepl\Deepl;

class GlossaryController extends Controller
{
    public function actionSync()
    {
        $data = Deepl::getInstance()->glossary->getSettings();
        foreach ($data as $glossary) {
            Deepl::getInstance()->glossary->createGlossary($glossary);
        }
    }

    public function actionDeleteAllFromApi()
    {
        $glossaries = Deepl::getInstance()->api->getAllGlossaries();
        foreach ($glossaries as $glossary) {
            Deepl::getInstance()->api->deleteGlossary($glossary->glossaryId);
        }
    }

    public function actionGetLanguagePairs()
    {
        $pairs = Deepl::getInstance()->api->getGlossaryPairs();
        dd($pairs);
    }
}
