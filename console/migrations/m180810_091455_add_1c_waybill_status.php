<?php

use yii\db\Migration;

/**
 * Class m180810_091455_add_1c_waybill_status
 */
class m180810_091455_add_1c_waybill_status extends Migration
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
        $this->insert('{{%one_s_waybill_status}}', ['denom' => 'Готова к выгрузке']);
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%one_s_waybill_status}}', ['denom' => 'Готова к выгрузке']);
    }
}