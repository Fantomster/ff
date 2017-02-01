<?php

use yii\db\Migration;

class m170201_135529_new_fields_for_whitelist_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'legal_entity', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'legal_address', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'signed', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_chat}}', 'danger');
    }
}
