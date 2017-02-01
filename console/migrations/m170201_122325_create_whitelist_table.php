<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `whitelist`.
 */
class m170201_122325_create_whitelist_table extends Migration
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
        $this->createTable('{{%white_list}}', [
            'id' => Schema::TYPE_PK,
            'organization_id' => Schema::TYPE_INTEGER  . ' not null',
            'info' => Schema::TYPE_TEXT  . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->addForeignKey('{{%wl_organization}}', '{{%white_list}}', 'organization_id', '{{%organization}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%wl_organization}}', '{{%white_list}}');
        $this->dropTable('{{%white_list}}');
    }
}
