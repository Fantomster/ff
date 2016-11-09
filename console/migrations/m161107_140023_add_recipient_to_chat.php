<?php

use yii\db\Migration;

class m161107_140023_add_recipient_to_chat extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_chat}}', 'recipient_id', $this->integer()->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_chat}}', 'recipient_id');
    }
}
