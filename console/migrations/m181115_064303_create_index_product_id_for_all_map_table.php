<?php

use yii\db\Migration;

/**
 * Handles the creation of table `index_product_id_for_all_map`.
 */
class m181115_064303_create_index_product_id_for_all_map_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_product_id_all_map', '{{%all_map}}', 'product_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_product_id_all_map', '{{%all_map}}');
    }
}
