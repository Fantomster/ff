<?php

use yii\db\Migration;

class m161107_071114_add_viewed_to_chat extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_chat}}', 'viewed', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_chat}}', 'viewed');
    }
}
