<?php

use yii\db\Migration;

class m161116_090113_profile_and_counter_new_columns extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%profile}}', 'avatar', $this->string()->null());
        $this->addColumn('{{%main_counter}}', 'next', $this->timestamp()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%profile}}', 'avatar');
        $this->dropColumn('{{%main_counter}}', 'next');
    }
}
