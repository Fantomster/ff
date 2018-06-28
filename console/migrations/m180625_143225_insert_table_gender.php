<?php

use yii\db\Migration;

class m180625_143225_insert_table_gender extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%gender}}', [
            'name_gender' => 'Мужской',
        ]);
        $this->insert('{{%gender}}', [
            'name_gender' => 'Женский',
        ]);
    }

    public function safeDown()
    {
        $this->execute('truncate table `gender`;');
    }

}
