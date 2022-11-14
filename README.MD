# DeepL integration for Craft CMS 4 

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


## Roadmap

- [ ] Support for different propagation methods for sections
- [ ] Support for different propagation methods for matrix fields

---
Brought to you by [Statik.be](https://www.statik.be)