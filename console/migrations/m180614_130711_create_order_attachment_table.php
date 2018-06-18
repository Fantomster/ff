<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_attachment`.
 */
class m180614_130711_create_order_attachment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_attachment', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'file' => $this->string(255)->notNull(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);
        $this->addForeignKey('{{%fk_order_attachment}}', '{{%order_attachment}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_order_attachment}}', '{{%order_attachment}}');
        $this->dropTable('order_attachment');
    }
}
