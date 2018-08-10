<?php

use yii\db\Migration;

/**
 * Handles the creation of table `iiko_selected_product`.
 */
class m180809_091758_create_iiko_selected_product_table extends Migration
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
        $this->createTable('iiko_selected_product', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'organization_id' => $this->integer()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('iiko_selected_product');
    }
}
