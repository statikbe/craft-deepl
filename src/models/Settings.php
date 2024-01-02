<?php

namespace statikbe\deepl\models;

use craft\base\Model;

class Settings extends Model
{
    public string $apiKey = '';

    public bool $primarySiteTranslation = false;
}
