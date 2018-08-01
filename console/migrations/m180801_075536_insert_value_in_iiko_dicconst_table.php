<?php

use yii\db\Migration;

/**
 * Class m180801_075536_insert_value_in_iiko_dicconst_table
 */
class m180801_075536_insert_value_in_iiko_dicconst_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('iiko_dicconst', ['denom' => 'available_stores_list', 'def_value' => '[]', 'comment' => 'Список доступных складов', 'type' => 5, 'is_active' => 1]);
    }

    public function safeDown()
    {
        $this->delete('iiko_dicconst', ['denom' => 'available_stores_list']);
    }
}
