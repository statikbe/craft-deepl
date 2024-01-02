<?php

namespace statikbe\deepl;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\log\MonologTarget;
use craft\services\UserPermissions;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use statikbe\deepl\models\Settings;
use statikbe\deepl\services\ApiService;
use statikbe\deepl\services\fields\ckeditor;
use statikbe\deepl\services\fields\configvaluesfield;
use statikbe\deepl\services\fields\cta;
use statikbe\deepl\services\fields\fields;
use statikbe\deepl\services\fields\positionfieldtype;
use statikbe\deepl\services\fields\redactor;
use statikbe\deepl\services\fields\seofields;
use statikbe\deepl\services\fields\seomatic;
use statikbe\deepl\services\fields\statik;
use statikbe\deepl\services\fields\supertable;
use statikbe\deepl\services\MapperService;
use yii\base\Event;

/**
 * @property ApiService api
 * @property MapperService mapper
 * @property redactor redactor
 * @property ckeditor ckeditor
 * @property fields fields
 * @property supertable supertable
 * @property configvaluesfield configvaluesfield
 * @property cta cta
 * @property positionfieldtype positionfieldtype
 * @property statik statik
 * @property seomatic seomatic;
 * @property seofields seofileds;
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
            function(DefineHtmlEvent $event) {
                /** @var Entry $entry */
//                if ($event->sender->getIsDraft()) {
//                    return;
//                }
                $template = Craft::$app->getView()->renderTemplate('deepl/_cp/_sidebar',
                    ["entry" => $event->sender, "settings" => $this->getSettings()]);
                $event->html .= $template;
            }
        );

        // Register a custom log target, keeping the format as simple as possible.
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'deepl',
            'categories' => ['deepl'],
            'level' => LogLevel::INFO,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "%datetime% %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);

        $this->registerPermissions();

        $this->setComponents([
            'api' => ApiService::class,
            'mapper' => MapperService::class,
            'redactor' => redactor::class,
            'ckeditor' => ckeditor::class,
            'fields' => fields::class,
            'supertable' => supertable::class,
            'configvaluesfield' => configvaluesfield::class,
            'cta' => cta::class,
            'positionfieldtype' => positionfieldtype::class,
            'statik' => statik::class,
            'seomatic' => seomatic::class,
            'seofields' => seofields::class,
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

    private function registerPermissions()
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {

                // Register our custom permissions
                $permissions = [
                    "heading" => Craft::t('deepl', 'DeepL Translator'),
                    "permissions" => [
                        'deepl:translate-entries' => [
                            'label' => Craft::t('deepl', 'Translate entries'),
                        ],
                    ],
                ];
                $event->permissions[Craft::t('deepl', 'DeepL')] = $permissions;
            }
        );
    }
}
