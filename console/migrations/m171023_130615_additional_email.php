<?php

use yii\db\Migration;

class m171023_130615_additional_email extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%additional_email}}',[
            'id' => $this->primaryKey(),
            'email' => $this->string()->null()->notNull(),
            'organization_id' => $this->integer()->notNull(),
            'order_created' => $this->integer()->defaultValue(1),
            'order_canceled' => $this->integer()->defaultValue(1),
            'order_changed' => $this->integer()->defaultValue(1),
            'order_processing' => $this->integer()->defaultValue(1),
            'order_done' => $this->integer()->defaultValue(1),
        ]);

        $this->addForeignKey('{{%additional_email_organization_id}}', '{{%additional_email}}', 'organization_id', '{{%organization}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%additional_email_organization_id}}', '{{%additional_email}}');
        $this->dropTable('{{%additional_email}}');
    }
}
