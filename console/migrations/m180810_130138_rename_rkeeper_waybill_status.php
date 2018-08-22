<?php

use yii\db\Migration;

/**
 * Class m180810_130138_rename_rkeeper_waybill_status
 */
class m180810_130138_rename_rkeeper_waybill_status extends Migration
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
        $this->update('{{%rk_waybillstatus}}', ['denom' => 'Готова к выгрузке'], ['id' => 1]);
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update('{{%rk_waybillstatus}}', ['denom' => 'К выгрузке'], ['id' => 1]);
    }
}