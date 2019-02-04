<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%preorder_content}}`.
 */
class m190204_110216_create_preorder_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';
        $this->createTable('{{%preorder_content}}', [
            'id' => $this->primaryKey(),
            'preorder_id' => $this->integer(11)->comment('id предзаказа из таблицы preorder'),
            'product_id' => $this->integer(11)->comment('id предзаказа из таблицы preorder'),
            'plan_quantity' => $this->decimal(20,3)->comment('планируемое для заказа количество'),
            'created_at'    => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время создания записи в таблице'),
            'updated_at'    => $this->timestamp()->null()->defaultValue(null)->comment('Дата и время последнего изменения записи в таблице'),
        ], $tableOptions);

        // add foreign key for table `preorder`
        $this->addForeignKey(
            'fk-preorder_content_preorder-preorder_id',
            'preorder_content',
            'preorder_id',
            'preorder',
            'id',
            'CASCADE'
        );

        // add foreign key for table `catalog_base_goods`
        $this->addForeignKey(
            'fk-preorder_catalog_base_goods-product_id',
            'preorder_content',
            'product_id',
            'catalog_base_goods',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops ForeignKey for column `preorder_id`
        $this->dropForeignKey(
            'fk-preorder_content_preorder-preorder_id',
            'preorder_content'
        );

        // drops ForeignKey for column `product_id`
        $this->dropForeignKey(
            'fk-preorder_catalog_base_goods-product_id',
            'preorder_content'
        );

        $this->dropTable('{{%preorder_content}}');
    }
}
