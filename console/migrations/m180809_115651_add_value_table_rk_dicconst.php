<?php

use yii\db\Migration;

class m180809_115651_add_value_table_rk_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('rk_dicconst', ['denom' => 'auto_unload_invoice', 'def_value' => '0', 'comment' => 'Автоматическая выгрузка накладных', 'type' => 1, 'is_active' => 1]);
    }

    public function safeDown()
    {
        $this->delete('rk_dicconst', ['denom' => 'auto_unload_invoice']);
    }
}
