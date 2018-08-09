<?php

use yii\db\Migration;

class m180809_120714_add_value_table_one_s_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('one_s_dicconst', ['denom' => 'auto_unload_invoice', 'def_value' => '0', 'comment' => 'Автоматическая выгрузка накладных', 'type' => 1, 'is_active' => 1]);
    }

    public function safeDown()
    {
        $this->delete('one_s_dicconst', ['denom' => 'auto_unload_invoice']);
    }
}
