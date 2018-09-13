<?php

use yii\db\Migration;

/**
 * Handles the creation of table `email_queue`.
 */
class m180912_063556_create_email_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%email_queue}}', [
            'id' => $this->primaryKey(),
            'to' => $this->string()->notNull(),
            'from' => $this->string()->notNull(),
            'subject' => $this->string()->null(),
            'body' => $this->text()->null(),
            'order_id' => $this->integer()->null(),
            'message_id' => $this->string()->unique()->null(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'email_fail_id' => $this->integer()->null(),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->null(),
        ], $tableOptions);
        
        $this->addForeignKey('{{%fk_email_order}}', '{{%email_queue}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('{{%fk_email_fail}}', '{{%email_queue}}', 'email_fail_id', '{{%email_fails}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_email_order}}', '{{%email_queue}}');
        $this->dropForeignKey('{{%fk_email_fail}}', '{{%email_queue}}');
        $this->dropTable('{{%email_queue}}');
    }
}
