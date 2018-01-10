<?php

use yii\db\Migration;

/**
 * Class m171222_070142_iiko_waybill_status
 */
class m171222_070142_iiko_waybill_status extends Migration
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
        $this->createTable('iiko_waybill_status',[
            'id' => $this->primaryKey(),
            'denom' => $this->string(),
            'comment' => $this->string()->null()
        ]);

        $this->insert('iiko_waybill_status', ['denom' => 'Сформирована']);
        $this->insert('iiko_waybill_status', ['denom' => 'Выгружена']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       $this->dropTable('iiko_waybill_status');
    }
}
