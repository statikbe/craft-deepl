<?php

namespace statikbe\deepl;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use statikbe\deepl\models\Settings;
use statikbe\deepl\services\ApiService;
use statikbe\deepl\services\fields\CKEditor;
use statikbe\deepl\services\fields\Fields;
use statikbe\deepl\services\fields\Redactor;
use statikbe\deepl\services\MapperService;
use yii\base\Event;


/**
 * @property ApiService api
 * @property MapperService mapper
 * @property Redactor redactor
 * @property CKEditor ckeditor
 * @property Fields fields
 */
class Deepl extends Plugin
{

    public bool $hasCpSection = false;

    public bool $hasCpSettings = true;

    public function init(): void
    {

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'statikbe\deepl\console\controllers';
        }
        parent::init();

        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_SIDEBAR_HTML,
            function (DefineHtmlEvent $event) {
                /** @var Entry $entry */
                if ($event->sender->getIsDraft()) {
                    return;
                }
                $template = Craft::$app->getView()->renderTemplate('deepl/_cp/_sidebar', ["entry" => $event->sender]);
                $event->html .= $template;
            }
        );

        $this->setComponents([
            'api' => ApiService::class,
            'mapper' => MapperService::class,
            'redactor' => Redactor::class,
            'ckeditor' => CKEditor::class,
            'fields' => Fields::class,
        ]);


    }

    protected function createSettingsModel(): Model
    {
        return new Settings();
    }

    protected function settingsHtml(): string
    {
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));
        return Craft::$app->view->renderTemplate(
            'deepl/_settings/_index',
            [
                'settings' => $this->getSettings(),
                'overrides' => $overrides,
            ]
        );
    }
}