<?php

use yii\db\Migration;

/**
 * Handles adding preorder_id to table `{{%order}}`.
 */
class m190204_111407_add_preorder_id_column_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'preorder_id', $this->integer(11)->comment('id предзаказа в таблице preorder'));

        // add foreign key for table `preorder`
        $this->addForeignKey(
            'fk-order_preorder-preorder_id',
            'order',
            'preorder_id',
            'preorder',
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
            'fk-order_preorder-preorder_id',
            'order'
        );

        $this->dropColumn('order', 'preorder_id');
    }
}
