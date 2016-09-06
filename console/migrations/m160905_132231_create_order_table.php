<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation for table `order`.
 */
class m160905_132231_create_order_table extends Migration
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
        $this->createTable('{{%order}}', [
            'id' => Schema::TYPE_PK,
            'client_id' => Schema::TYPE_INTEGER  . ' not null',
            'vendor_id' => Schema::TYPE_INTEGER  . ' not null',
            'created_by_id' => Schema::TYPE_INTEGER  . ' not_null',
            'accepted_by_id' => Schema::TYPE_INTEGER . ' not null',
            'status' => Schema::TYPE_INTEGER . ' not null',
            'total_price' => Schema::TYPE_DECIMAL . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->createTable('{{%order_content}}', [
            'order_id' => Schema::TYPE_INTEGER . ' not null',
            'product_id' => Schema::TYPE_INTEGER . ' not null',
            'quantity' => Schema::TYPE_INTEGER . ' not null',
            'price' => Schema::TYPE_DECIMAL . ' not null',
        ], $tableOptions);
        $this->addForeignKey('{{%client}}', '{{%order}}', 'client_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%vendor}}', '{{%order}}', 'vendor_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%created_by}}', '{{%order}}', 'created_by_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%accepted_by}}', '{{%order}}', 'accepted_by_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%order}}', '{{%order_content}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('{{%product}}', '{{%order_content}}', 'product_id', '{{%catalog_base_goods}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%product}}', '{{%order_content}}');
        $this->dropForeignKey('{{%order}}', '{{%order_content}}');
        $this->dropForeignKey('{{%accepted_by}}', '{{%order}}');
        $this->dropForeignKey('{{%created_by}}', '{{%order}}');
        $this->dropForeignKey('{{%vendor}}', '{{%order}}');
        $this->dropForeignKey('{{%client}}', '{{%order}}');
        $this->dropTable('{{%order}}');
    }
}
