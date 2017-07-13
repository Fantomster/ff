<?php

use yii\db\Migration;

class m170713_130932_franchisee_add_col extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%franchisee}}', 'additional_number_manager', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%franchisee}}', 'additional_number_manager');
    }
}
