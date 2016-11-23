<?php

use yii\db\Migration;

class m161123_132533_add_danger_to_chat extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%order_chat}}', 'danger', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_chat}}', 'danger');
    }
}
