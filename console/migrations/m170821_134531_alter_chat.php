<?php

use yii\db\Migration;

class m170821_134531_alter_chat extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order_chat}}', 'message', $this->text()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order_chat}}', 'message', $this->string()->null());
    }
}
