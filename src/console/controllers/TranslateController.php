<?php

namespace statikbe\deepl\console\controllers;

use craft\console\Controller;
use Craft;
use craft\elements\Entry;
use statikbe\deepl\Deepl;

class TranslateController extends Controller
{
    public function actionTest()
    {

    }

    public function actionShowAvailableSourceLanguages()
    {
        dd(Deepl::getInstance()->api->getSourceLanguages());
    }

    public function actionShowAvailableTargetLanguages()
    {
        dd(Deepl::getInstance()->api->getTargetLanguages());
    }
}