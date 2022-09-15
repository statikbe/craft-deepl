# DeepL integration for Craft CMS 4 

The goal of this plugin is to let [DeepL](https://www.deepl.com/) handle 95% of the translation work, after which you can review the content and change it to your own tone of voice and specific industry jargon.

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

When you click the translate button, **a new draft will be created** for that entry, so you'll have overwrite existing content or publish your translation right away.

It's up to you to check the translations and the rest of the fields and to apply the draft when you're satisfied with the result.

> **Warning**
> The plugin does not keep track of which fields or entries have been translated previously. If you click the translation button again, after you've made changes in the destination language, those will be discarded in the newly created draft.

---

## Requirements

- Craft CMS 4.0.0 or later.
- a [DeepL API key](https://www.deepl.com/pro-api?cta=header-pro-api/)

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

#### Third party fields
- craftcms/redactor
- craftcms/ckeditor
- verbb/supertable 
- hybridinteractive/craft-position-fieldtype
- statikbe/craft-config-values 
- statikbe/craft-cta-field


## Roadmap

- [ ] Support for different propagation methods for sections
- [ ] Support for different propagation methods for matrix fields

---
Brought to you by [Statik.be](https://www.statik.be)