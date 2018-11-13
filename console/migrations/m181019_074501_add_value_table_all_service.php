<?php

use yii\db\Migration;

class m181019_074501_add_value_table_all_service extends Migration
{
    public $tableName = '{{%all_service}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'type_id' => 1,
            'is_active' => 1,
            'denom' => 'Tillypad',
            'vendor' => 'Tillypad',
            'created_at' => '2018-10-19 10:54:00',
            'log_table' => 'iiko_log',
            'log_field' => 'guide',
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['denom' => 'Tillypad']);
    }
}
