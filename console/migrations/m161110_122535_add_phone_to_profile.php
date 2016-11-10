<?php

use yii\db\Migration;

class m161110_122535_add_phone_to_profile extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%profile}}', 'phone', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%profile}}', 'phone');
    }
}
