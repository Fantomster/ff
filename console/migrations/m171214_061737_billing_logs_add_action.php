<?php

use yii\db\Migration;

class m171214_061737_billing_logs_add_action extends Migration
{
    public $tableName = 'billing_logs';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'action', $this->string()->null());
        $this->addColumn($this->tableName, 'status', $this->string()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'action');
        $this->dropColumn($this->tableName, 'status');
    }
}
