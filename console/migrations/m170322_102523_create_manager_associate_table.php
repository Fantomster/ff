<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `manager_associate`.
 */
class m170322_102523_create_manager_associate_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%relation_supp_rest}}', 'vendor_manager_id');

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%manager_associate}}', [
            'id' => Schema::TYPE_PK,
            'manager_id' => Schema::TYPE_INTEGER . ' not null',
            'organization_id' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions);
        $this->addForeignKey('{{%fk_manager_assoc}}', '{{%manager_associate}}', 'manager_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%fk_manager_assoc_client}}', '{{%manager_associate}}', 'organization_id', '{{%organization}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%relation_supp_rest}}', 'vendor_manager_id', $this->integer()->null());
        $this->dropForeignKey('{{%fk_manager_assoc}}', '{{%manager_associate}}');
        $this->dropForeignKey('{{%fk_manager_assoc_client}}', '{{%manager_associate}}');
        $this->dropTable('{{%manager_associate}}');
    }
}
