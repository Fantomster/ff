<?php

use yii\db\Migration;

class m170614_103230_franchisee_settings extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%franchisee}}', 'fio_manager', $this->string()->null());
        $this->addColumn('{{%franchisee}}', 'phone_manager',$this->string()->null());
        $this->addColumn('{{%franchisee}}', 'picture_manager', $this->string()->null());
    }

    public function safeDown() {
        $this->dropColumn('{{%franchisee}}', 'fio_manager');
        $this->dropColumn('{{%franchisee}}', 'phone_manager');
        $this->dropColumn('{{%franchisee}}', 'picture_manager');
    }
}
