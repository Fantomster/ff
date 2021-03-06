<?php

use yii\db\Migration;

/**
 * Handles the creation of table `iiko_selected_store`.
 */
class m180809_091817_create_iiko_selected_store_table extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('iiko_selected_store', [
            'id' => $this->primaryKey(),
            'store_id' => $this->integer()->notNull(),
            'organization_id' => $this->integer()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('iiko_selected_store');
    }
}
