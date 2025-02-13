<?php

namespace statikbe\deepl\models;

use craft\base\Model;

class Settings extends Model
{
    public string $apiKey = '';

    public bool $translateSlugs = true;

    public bool $primarySiteTranslation = false;

    public bool $translateAcrossSiteGroups = false;

    public bool $copyContent = false;

    public array $glossaries = [];
}
