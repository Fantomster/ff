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
        $this->addColumn('{{%white_list}}', 'legal_email', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'inn', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'kpp', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'ogrn', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'bank_name', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'bik', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'correspondent_account', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'checking_account', $this->string()->null());
        $this->addColumn('{{%white_list}}', 'phone', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%white_list}}', 'signed');
        $this->dropColumn('{{%white_list}}', 'legal_entity');
        $this->dropColumn('{{%white_list}}', 'legal_address');
        $this->dropColumn('{{%white_list}}', 'legal_email');
        $this->dropColumn('{{%white_list}}', 'inn');
        $this->dropColumn('{{%white_list}}', 'kpp');
        $this->dropColumn('{{%white_list}}', 'ogrn');
        $this->dropColumn('{{%white_list}}', 'bank_name');
        $this->dropColumn('{{%white_list}}', 'bik');
        $this->dropColumn('{{%white_list}}', 'correspondent_account');
        $this->dropColumn('{{%white_list}}', 'checking_account');
        $this->dropColumn('{{%white_list}}', 'phone');
    }
}
