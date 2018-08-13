<?php

use yii\db\Migration;

/**
 * Class m180813_080402_rename_and_add_status_rkeeper
 */
class m180813_080402_rename_and_add_status_rkeeper extends Migration
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
        $this->update('{{%rk_waybillstatus}}', ['denom' => 'Сформирована'], ['id' => 1]);
        $this->insert('{{%rk_waybillstatus}}', ['denom' => 'Готова к выгрузке']);
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update('{{%rk_waybillstatus}}', ['denom' => 'Сформирована'], ['id' => 1]);
        $this->delete('{{%rk_waybillstatus}}', ['denom' => 'Готова к выгрузке']);
    }
}