<?php

use yii\db\Migration;

class m170718_141657_email_update extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%email_fails}}', [
            'id' => $this->primaryKey(),
            'email_notification_id' => $this->integer()->notNull(),
            'type' => $this->integer()->null(),
            'email' => $this->string()->notNull(),
            'body' => $this->text()->notNull(),
        ], $tableOptions);
        $this->createTable('{{%email_notification}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
            'orders' => $this->boolean()->notNull()->defaultValue(false),
            'requests' => $this->boolean()->notNull()->defaultValue(false),
            'changes' => $this->boolean()->notNull()->defaultValue(false),
            'invites' => $this->boolean()->notNull()->defaultValue(false),
            'last_fail' => $this->integer()->null(),
        ], $tableOptions);
        $this->createTable('{{%sms_notification}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
            'orders' => $this->boolean()->notNull()->defaultValue(false),
            'requests' => $this->boolean()->notNull()->defaultValue(false),
            'changes' => $this->boolean()->notNull()->defaultValue(false),
            'invites' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

        $this->addForeignKey('{{%fk_email_fails}}', '{{%email_fails}}', 'email_notification_id', '{{%email_notification}}', 'id');
        $this->addForeignKey('{{%fk_email_notification}}', '{{%email_notification}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%fk_sms_notification}}', '{{%sms_notification}}', 'user_id', '{{%user}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_email_fails}}', '{{%email_fails}}');
        $this->dropForeignKey('{{%fk_email_notification}}', '{{%email_notification}}');
        $this->dropForeignKey('{{%fk_sms_notification}}', '{{%sms_notification}}');
        $this->dropTable('{{%email_notification}}');
        $this->dropTable('{{%sms_notification}}');
        $this->dropTable('{{%email_fails}}');
    }
}
