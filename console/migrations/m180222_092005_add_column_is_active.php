<?php

use yii\db\Migration;

/**
 * Class m180222_092005_add_column_is_active
 */
class m180222_092005_add_column_is_active extends Migration
{
    public $tableName = 'integration_setting_from_email';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'is_active', $this->integer()->after('password')->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'is_active');
    }
}
