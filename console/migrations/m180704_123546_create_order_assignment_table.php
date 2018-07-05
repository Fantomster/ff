<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_assignment`.
 */
class m180704_123546_create_order_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_assignment', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'assigned_to' => $this->integer()->notNull(),
            'assigned_by' => $this->integer()->notNull(),
            'is_processed' => $this->integer(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'processed_at' => $this->timestamp()->null(),
        ]);
        $this->addForeignKey('{{%fk_order_assignment}}', '{{%order_assignment}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('{{%fk_order_assigned_to}}', '{{%order_assignment}}', 'assigned_to', '{{%user}}', 'id');
        $this->addForeignKey('{{%fk_order_assigned_by}}', '{{%order_assignment}}', 'assigned_by', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_order_assignment}}', '{{%order_assignment}}');
        $this->dropForeignKey('{{%fk_order_assigned_to}}', '{{%order_assignment}}');
        $this->dropForeignKey('{{%fk_order_assigned_by}}', '{{%order_assignment}}');
        $this->dropTable('order_assignment');
    }
}
