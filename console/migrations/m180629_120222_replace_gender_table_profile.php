<?php

use yii\db\Migration;

class m180629_120222_replace_gender_table_profile extends Migration
{
    public function safeUp()
    {
        $this->update('{{%profile}}', array(
            'gender' => 0),
            'gender is null'
        );
    }

    public function safeDown()
    {
        $this->update('{{%profile}}', array(
            'gender' => null),
            'gender=0'
        );
    }

}
