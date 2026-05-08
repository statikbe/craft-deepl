# DeepL integration for Craft CMS

The goal of this plugin is to let <a href="https://www.deepl.com/" target="_blank" rel="noopener">DeepL</a> handle 95% of the translation work, after which you can review the content and change it to your own tone of voice and specific industry jargon.

__It is not meant to be a single click all encompassing translation tool.__

The plugin will translate supported text fields and will copy other field types without interfering with them. For example:
a plain text field and a Redactor field will get translated, a Categories and a dropdown field will keep the same value.

_The plugin is not affiliated or linked to DeepL in any way_

## Workflow

When the plugin is enabled, you'll see a new block in the sidebar of the entry edit page.
Depending on how many languages your CMS install has, you'll see more translation options.

The plugin doesn't  support translating specific fields or texts, **only all fields at once**. So we recommend the following workflow:
- Finish the entry 1 language
- Switch to the language to which you want to translate
- Click the option for the language from which you want to translate (the one you finished in the first step)

When you click the ‘translate to’ button in your source entry (e.g. English version is source content), a draft version of the ‘translate to language’ (e.g. Dutch) will be created for that entry. 
In this draft version (Dutch), all text fields of the source language entry will be translated into your chosen language (Dutch) via Deepl. Content that already existed in the new language (Dutch in this example) for the entry, will be overwritten when using the "translate via Deepl" button.

Once the draft version with Deepl translations is created, it’s up to the content manager to check and refine the translations, as well as the non-text-fields, and to publish the draft when satisfied with the result.

> **Warning**
> Warning: The plugin does not keep track of which fields or entries have been translated in earlier stages. If you have made changes in the new language (Dutch in our example), and then click the translation button again (from the English source content to the Dutch translated language), all changes previously made in the destination language entry will be discarded in the newly created draft.

---

## Requirements

- Craft CMS 4.0.0 or later.
- a <a href="https://www.deepl.com/pro-api?cta=header-pro-api/" target="_blank" rel="noopener">DeepL API key</a>

> **Note**
> Using the plugin is only possible with a Deepl API key, which will require your payment details. The current pricing model of Deepl allows a certain number of monthly translations at no cost. 
> The plugin maintainers are not responsible for any possible change in pricing model Deepl would make.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

```bash
# go to the project directory
cd /path/to/my-craft-project.dev

# tell Composer to install the plugin
composer require statikbe/craft-deepl

# tell Craft to install the plugin
php craft install/plugin deepl
```

## Glossaries
You can add glossaries for certain language pairs to aid translation of jargon or sector specific terms.
This can be done through the ``deepl.php`` config file, like the example bellow:

````php
<?php

return [
    'glossaries' => [
        [
            'name' => 'Glossary 1',
            'source' => 'nl',
            'target' => 'fr',
            'entries' => [
                "Word NL" => "Word FR",
            ]
        ]
    ]
];
````

Note that DeepL does not support country specific language variants here. So you can not create a glossary to transate from ``nl-BE`` to ``nl-NL``.

### Creating a glossary
Once you have you config file, run the sync command on command line to add your glossaries to DeepL. You can run this in production or locally - as long as your using the same API key in both places
````bash
php craft deepl/glossary/sync
````

### Updating a glossary
If you have the make changes to the glossary at the later point in time, simply run the sync command again and the plug-in will replace the glossary you previously created with the new one.

## Copy content (1.5.0 and later)
If you simply want to copy content from another entry, without translating, you can enable the "Copy content" setting and a new set of buttons will appear.
Not that this runs through the same and thus it is limited to the same supported fields as the translate function.

## Supported Fields
The plugin currently supports (for either translation or for copying values)

#### Core Craft fields:
- Plain Text
- Matrix
- Assets
- Entry

#### Third party fields
- craftcms/redactor
- craftcms/ckeditor
- verbb/supertable 
- hybridinteractive/craft-position-fieldtype
- statikbe/craft-config-values 
- statikbe/craft-cta-field
- studioespresso/craft-seo-fields
- nystudio107/craft-seomatic


## Custom translation options (events)

The plugin fires `ApiService::EVENT_BEFORE_TRANSLATE` right before each request to DeepL. Listeners receive a `ModifyTranslateOptionsEvent` and can mutate the options sent to the API — including DeepL's [custom instructions](https://developers.deepl.com/docs/best-practices/custom-instructions) (up to 10 directives, ≤300 chars each), `context`, `formality`, `style_id`, and any other [`TranslateTextOptions`](https://github.com/DeepLcom/deepl-php/blob/main/src/TranslateTextOptions.php) key.

This is intentionally event-based rather than a CP setting: instructions tend to be project-specific (brand voice, jargon, sector tone), so they live in your project module instead of the plugin database.

### Event payload

| Property | Type | Mutable? | Notes |
|---|---|---|---|
| `sourceLang` | `string` | no | Source language as Craft passes it (`en`, `nl-BE`, …). |
| `targetLang` | `string` | no | Target language. |
| `text` | `string \| array<string>` | no | Provided for context. `array` when triggered from the batch flush. |
| `options` | `array<string, mixed>` | yes | Full DeepL options bag — already contains `tag_handling` and (if matched) `glossary`. |
| `customInstructions` | `array<int, string>` | yes | Convenience bucket. Merged into `options['custom_instructions']` and capped to 10. |

### Basic example

Register a listener in your project module's `init()`:

````php
use DeepL\TranslateTextOptions;
use statikbe\deepl\events\ModifyTranslateOptionsEvent;
use statikbe\deepl\services\ApiService;
use yii\base\Event;

Event::on(
    ApiService::class,
    ApiService::EVENT_BEFORE_TRANSLATE,
    function (ModifyTranslateOptionsEvent $event) {
        if ($event->targetLang === 'fr') {
            $event->customInstructions[] = 'Use a friendly, informal tone (tutoiement).';
            $event->customInstructions[] = 'Translate brand names literally; do not localize.';
        }

        // Or set any other DeepL option directly:
        $event->options[TranslateTextOptions::FORMALITY] = 'less';
    }
);
````

### Branching on the current entry

The event itself is per-translation, not per-entry, but you can read the controller request to scope rules to a section or entry type:

````php
Event::on(
    ApiService::class,
    ApiService::EVENT_BEFORE_TRANSLATE,
    function (ModifyTranslateOptionsEvent $event) {
        $entryId = Craft::$app->getRequest()->getParam('entryId');
        $entry = $entryId ? Craft::$app->getEntries()->getEntryById((int) $entryId) : null;

        if ($entry?->section?->handle === 'legal') {
            $event->customInstructions[] = 'Preserve all legal terminology verbatim; do not paraphrase.';
            $event->options[TranslateTextOptions::FORMALITY] = 'more';
        }
    }
);
````

### Notes & limits

- DeepL caps custom instructions at **10 per request, 300 chars each**. The plugin enforces the count cap; it does not truncate per-instruction length — DeepL will return an error if you exceed it.
- Setting `custom_instructions` automatically routes to DeepL's `quality_optimized` model. Do **not** combine it with `model_type: latency_optimized`.
- Custom instructions only support these target languages: `de`, `en`, `es`, `fr`, `it`, `ja`, `ko`, `zh` (and variants).
- `custom_instructions` is supported on text translation only, not the `/document` endpoint.
- Phrasing matters — the [DeepL guidance](https://developers.deepl.com/docs/best-practices/custom-instructions) recommends positive, single-rule directives ("Use a formal tone") over negatives or compound rules.

## Roadmap

- [ ] Support for different propagation methods for sections
- [ ] Support for different propagation methods for matrix fields

---
Brought to you by [Statik.be](https://www.statik.be)