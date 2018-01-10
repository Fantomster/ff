<?php

use yii\db\Migration;

/**
 * Class m171221_093131_rename_column
 */
class m171221_093131_rename_column extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('iiko_category', 'active', 'is_active');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->renameColumn('iiko_category', 'is_active', 'active');
    }
}
