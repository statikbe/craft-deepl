<?php

namespace statikbe\deepl\records;


class GlossaryRecord extends \craft\db\ActiveRecord
{

    public static function tableName(): string
    {
        return 'deepl_glossaries';
    }

}