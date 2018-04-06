<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_sequence`.
 */
class m180406_122637_create_order_sequence_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_sequence', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull()
        ]);
        $this->addForeignKey('relation_order_order_sequence', 'order_sequence', 'order_id', 'order', 'id', 'CASCADE');
        $this->createIndex('unique_order_id', 'order_sequence', ['order_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('relation_order_order_sequence', 'order_sequence');
        $this->dropTable('order_sequence');
    }
}
