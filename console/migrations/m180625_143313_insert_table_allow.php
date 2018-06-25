<?php

use yii\db\Migration;

class m180625_143313_insert_table_allow extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%allow}}', [
            'name_allow' => 'Согласен',
        ]);
        $this->insert('{{%allow}}', [
            'name_allow' => 'Не согласен',
        ]);
    }

    public function safeDown()
    {
        $this->execute('truncate table `allow`;');
    }

}
