<?php

use yii\db\Migration;

class m190214_094856_set_value_is_active_table_rk_product extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%rk_product}}', array(
            'is_active' => 1)
        );
    }

    public function safeDown()
    {
        $this->update('{{%rk_product}}', array(
            'is_active' => null)
        );
    }
}
