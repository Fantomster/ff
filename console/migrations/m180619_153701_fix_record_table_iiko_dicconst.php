<?php

use yii\db\Migration;

class m180619_153701_fix_record_table_iiko_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%iiko_dicconst}}', array(
            'comment' => 'Состояние колонки "№ накладной"'),
            'id=5'
        );

    }

    public function safeDown()
    {
        echo "m180619_153701_fix_record_table_iiko_dicconst cannot be reverted.\n";

        return false;
    }

}
