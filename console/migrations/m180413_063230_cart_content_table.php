<?php

use yii\db\Migration;

/**
 * Class m180413_063230_cart_content_table
 */
class m180413_063230_cart_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cart_content}}', [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer()->notNull(),
            'vendor_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'product_name' => $this->string(),
            'quantity' => $this->float(),
            'price' => $this->double(),
            'units' => $this->float(),
            'comment' => $this->text(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('{{%cart_content_index_cart_id}}', '{{%cart_content}}', 'cart_id');

        $this->addForeignKey('{{%cart_content_relation_cart_id}}', '{{%cart_content}}', 'cart_id', '{{%cart}}', 'id');
        $this->addForeignKey('{{%cart_content_relation_vendor_id}}', '{{%cart_content}}', 'vendor_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%cart_content_relation_product_id}}', '{{%cart_content}}', 'product_id', '{{%catalog_base_goods}}', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%cart_content_relation_cart_id}}', '{{%cart_content}}');
        $this->dropForeignKey('{{%cart_content_relation_vendor_id}}', '{{%cart_content}}');
        $this->dropForeignKey('{{%cart_content_relation_product_id}}', '{{%cart_content}}');
        $this->dropIndex('{{%cart_content_index_cart_id}}', '{{%cart_content}}');
        $this->dropTable('{{%cart_content}}');
    }

}
