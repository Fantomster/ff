<?php

use yii\db\Migration;

/**
 * Class m180808_144204_add_waybill_status
 */
class m180808_144204_add_waybill_status extends Migration
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
        $this->insert('{{%iiko_waybill_status}}', ['denom' => 'Отправляется']);
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%iiko_waybill_status}}', ['denom' => 'Отправляется']);
    }
}
