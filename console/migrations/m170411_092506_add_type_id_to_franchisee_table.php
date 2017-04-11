<?php

use yii\db\Migration;

class m170411_092506_add_type_id_to_franchisee_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%franchisee}}', 'type_id', $this->integer()->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%franchisee}}', 'type_id');
    }
}
