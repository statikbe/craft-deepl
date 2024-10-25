<?php

namespace statikbe\deepl\migrations;

use craft\db\Migration;
use statikbe\deepl\records\GlossaryRecord;

/**
 * m240726_134638_addGlossaryTable migration.
 */
class m240726_134638_addGlossaryTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(GlossaryRecord::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'source' => $this->string()->notNull(),
            'target' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()->notNull(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240726_134638_addGlossaryTable cannot be reverted.\n";
        return false;
    }
}
