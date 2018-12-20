<?php

use yii\db\Migration;

/**
 * Handles the creation of table `index_for_inn_in_vetis_business_entity`.
 */
class m181209_031805_create_index_for_inn_in_vetis_business_entity_table extends Migration
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
        $this->createIndex('idx_inn_vetis_business_entity', '{{%vetis_business_entity}}', 'inn');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_inn_vetis_business_entity', '{{%vetis_business_entity}}');
    }
}
