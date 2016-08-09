<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `organization`.
 */
class m160808_094240_create_organization_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%organization}}', [
            'id' => Schema::TYPE_PK,
            'type_id' => Schema::TYPE_INTEGER . ' not null',
            'name' => Schema::TYPE_STRING . ' not null',
            'city' => Schema::TYPE_STRING . ' null',
            'address' => Schema::TYPE_STRING . ' null',
            'zip_code' => Schema::TYPE_STRING . ' null',
            'phone' => Schema::TYPE_STRING . ' null',
            'email' => Schema::TYPE_STRING . ' null',
            'website' => Schema::TYPE_STRING . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->createTable('{{%organization_type}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' not null',
        ], $tableOptions);
        $this->addColumn('{{%user}}', 'organization_id', $this->integer()->null());
        $this->addColumn('{{%role}}', 'can_manage', $this->integer()->defaultValue(0));
        $this->addColumn('{{%role}}', 'organization_type', $this->integer()->null());

        $this->addForeignKey('{{%role_org}}', '{{%role}}', 'organization_type', '{{%organization_type}}', 'id');
        $this->addForeignKey('{{%type}}', '{{%organization}}', 'type_id', '{{%organization_type}}', 'id');
        $this->addForeignKey('{{%organization}}', '{{%user}}', 'organization_id', '{{%organization}}', 'id');
        
        $this->batchInsert('{{%organization_type}}', ['name'], [
            ['Ресторан',], ['Поставщик',], 
        ]);
        $this->batchInsert('{{%role}}', ['name', 'can_admin', 'can_manage', 'organization_type'], [
            ['Менеджер', 0, 1, 1],
            ['Работник', 0, 0, 1],
            ['Менеджер', 0, 1, 2],
            ['Работник', 0, 0, 2],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%organization}}', '{{%user}}');
        $this->dropForeignKey('{{%role_org}}', '{{%role}}');
        $this->dropColumn('{{%user}}', 'organization_id');
        $this->dropColumn('{{%role}}', 'organization_type');
        $this->dropColumn('{{%role}}', 'can_manage');
        $this->delete('{{%role}}', ['name' => ['Менеджер', 'Работник']]);
        $this->dropTable('{{%organization}}');
        $this->dropTable('{{%organization_type}}');
    }
}
