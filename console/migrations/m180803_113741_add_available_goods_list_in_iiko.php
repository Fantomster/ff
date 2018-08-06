<?php

use yii\db\Migration;

/**
 * Class m180803_113741_add_available_goods_list_in_iiko
 */
class m180803_113741_add_available_goods_list_in_iiko extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('iiko_dicconst', ['denom' => 'available_goods_list', 'def_value' => '[]', 'comment' => 'Список доступных товаров', 'type' => 6, 'is_active' => 1]);
    }

    public function safeDown()
    {
        $this->delete('iiko_dicconst', ['denom' => 'available_goods_list']);
    }
}
