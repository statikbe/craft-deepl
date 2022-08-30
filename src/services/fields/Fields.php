<?php

namespace statikbe\deepl\services\fields;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\fields\Email;
use craft\fields\Matrix;
use craft\fields\PlainText;
use craft\fields\Url;
use craft\models\Site;
use statikbe\deepl\Deepl;


class Fields extends Component
{

    /**
     * @param PlainText $field
     * @param Entry $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return false|string
     * @throws \craft\errors\InvalidFieldException
     */
    public function PlainText(PlainText $field, Entry $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        $content = $sourceEntry->getFieldValue($field->handle);
        if (!$content) {
            return false;
        }

        return Deepl::getInstance()->api->translateString(
            $sourceEntry->getFieldValue($field->handle),
            $sourceSite->language,
            $targetSite->language
        );
    }


    /**
     * @param Email $field
     * @param Entry $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return mixed
     * @throws \craft\errors\InvalidFieldException
     */
    public function Email(Email $field, Entry $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        return $sourceEntry->getFieldValue($field->handle);
    }


    /**
     * @param Url $field
     * @param Entry $sourceEntry
     * @param Site $sourceSite
     * @param Site $targetSite
     * @return mixed
     * @throws \craft\errors\InvalidFieldException
     */
    public function Url(Url $field, Entry $sourceEntry, Site $sourceSite, Site $targetSite)
    {
        return $sourceEntry->getFieldValue($field->handle);
    }


    /**
     * @param Matrix $field
     */
    public function Matrix($field, $entry)
    {

    }


}