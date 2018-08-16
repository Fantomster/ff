<?php

use yii\db\Migration;

class m180816_085025_update_value_table_one_s_dic extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%one_s_dic}}', array(
            'dicstatus_id' => 3),
            'obj_count=0'
        );

    }

    public function safeDown()
    {
        echo "m180816_085025_update_value_table_one_s_dic cannot be reverted.\n";

        return false;
    }
}
