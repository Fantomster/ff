<?php

use yii\db\Migration;
use yii\db\Schema;

class m170216_122348_create_franchisee extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%franchisee}}', [
            'id' => Schema::TYPE_PK,
            'signed' => Schema::TYPE_STRING . ' null',
            'legal_entity' => Schema::TYPE_STRING . ' null',
            'legal_address' => Schema::TYPE_STRING . ' null',
            'legal_email' => Schema::TYPE_STRING . ' null',
            'inn' => Schema::TYPE_STRING . ' null',
            'kpp' => Schema::TYPE_STRING . ' null',
            'ogrn' => Schema::TYPE_STRING . ' null',
            'bank_name' => Schema::TYPE_STRING . ' null',
            'bik' => Schema::TYPE_STRING . ' null',
            'phone' => Schema::TYPE_STRING . ' null',
            'correspondent_account' => Schema::TYPE_STRING . ' null',
            'checking_account' => Schema::TYPE_STRING . ' null',
            'info' => Schema::TYPE_TEXT . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->createTable('{{%franchisee_associate}}', [
            'id' => Schema::TYPE_PK,
            'franchisee_id' => Schema::TYPE_INTEGER . ' not null',
            'organization_id' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions);
        $this->createTable('{{%franchisee_user}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' not null',
            'franchisee_id' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions);
        $this->addForeignKey('{{%fk_franch_assoc}}', '{{%franchisee_associate}}', 'franchisee_id', '{{%franchisee}}', 'id');
        $this->addForeignKey('{{%fk_franch_assoc_org}}', '{{%franchisee_associate}}', 'organization_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%fk_franch_assoc_profile}}', '{{%franchisee_user}}', 'franchisee_id', '{{%franchisee}}', 'id');
        $this->addForeignKey('{{%fk_franch_assoc_user}}', '{{%franchisee_user}}', 'user_id', '{{%user}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_franch_assoc}}', '{{%franchisee_associate}}');
        $this->dropForeignKey('{{%fk_franch_assoc_org}}', '{{%franchisee_associate}}');
        $this->dropForeignKey('{{%fk_franch_assoc_profile}}', '{{%franchisee_user}}');
        $this->dropForeignKey('{{%fk_franch_assoc_user}}', '{{%franchisee_user}}');
        $this->dropTable('{{%franchisee_user}}');
        $this->dropTable('{{%franchisee_associate}}');
        $this->dropTable('{{%franchisee}}');
    }
}
