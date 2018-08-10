<?php

use yii\db\Migration;

/**
 * Class m180810_083021_add_iiko_waybill_status
 */
class m180810_083021_add_iiko_waybill_status extends Migration
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
        $this->insert('{{%iiko_waybill_status}}', ['denom' => 'Готова к выгрузке']);
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%iiko_waybill_status}}', ['denom' => 'Готова к выгрузке']);
    }
}