# DeepL integration for Craft CMS 4 

The goal of this plugin is to let DeepL handle 95% of the translation work, after which you can review the text and change it to your own tone of voice and specific industry jargon.

__It is not meant to be a click and forget translation tool.__

The plugin will translate supported text fields and will copy other field types without interfering with them. For example:
a plain text and a Redactor field will get translated, a Categories and a dropdown field will keep your same value.

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