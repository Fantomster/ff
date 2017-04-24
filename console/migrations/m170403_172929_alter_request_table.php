<?php

use yii\db\Migration;

class m170403_172929_alter_request_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%request_callback}}', 'price', $this->decimal(10,2)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%request_callback}}', 'price', $this->integer()->notNull());
    }
}
