<?php

use yii\db\Migration;

/**
 * Class m180817_071403_add_status_for_product_in_waybill
 */
class m180817_071403_add_status_for_product_in_waybill extends Migration
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
        $this->addColumn('{{%iiko_waybill_data}}', 'unload_status', $this->integer()->notNull()->defaultValue(1));
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_waybill_data}}', 'unload_status');
    }
}
