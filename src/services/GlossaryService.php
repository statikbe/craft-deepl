<?php

namespace statikbe\deepl\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\errors\InvalidFieldException;
use statikbe\deepl\Deepl;
use statikbe\deepl\models\GlossaryModel;
use statikbe\deepl\records\GlossaryRecord;
use yii\log\Logger;

class GlossaryService extends Component
{
    public function init(): void
    {
    }

    public function getSettings()
    {
        $data = Deepl::getInstance()->getSettings()->glossaries;
        $items = [];
        foreach ($data as $glossary) {
            $model = new GlossaryModel();
            $model->populate($glossary);
            if ($model->validate()) {
                $items[] = $model;
            }
        }
        return $items;
    }

    public function getGlossaryForLanguages($source, $target)
    {
        $record = GlossaryRecord::find()->andWhere(['source' => $source, 'target' => $target])->one();
        if($record) {
            return $record->uid;
        }

        return false;
    }

    public function createGlossary(GlossaryModel $model)
    {
        // if we already have a glossary we should probably delete it first?

        // We already have a glossery so we'll remove it from deepl first so we can update it with the new data
        $result = GlossaryRecord::findOne(['target' => $model->target, 'source' => $model->source]);
        if ($result) {
            Deepl::getInstance()->api->deleteGlossary($result->uid);
            $record = $result;
        } else {
            $record = new GlossaryRecord();
        }

        // Create a new glossary before saving it locally
        $glossary = Deepl::getInstance()->api->createGlossary($model);

        $record->name = $model->name;
        $record->target = $model->target;
        $record->source = $model->source;
        $record->uid = $glossary->glossaryId;
        $record->save();
    }

}
