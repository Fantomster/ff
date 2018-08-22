<?php

use yii\db\Migration;

/**
 * Class m180822_083610_add_status_column_for_rk_waybill_data
 */
class m180822_083610_add_status_column_for_rk_waybill_data extends Migration
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
        $this->addColumn('{{%rk_waybill_data}}', 'unload_status', $this->integer()->notNull()->defaultValue(1));
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_waybill_data}}', 'unload_status');
    }
}
