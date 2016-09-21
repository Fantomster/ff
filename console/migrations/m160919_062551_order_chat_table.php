<?php

use yii\db\Migration;
use yii\db\Schema;

class m160919_062551_order_chat_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%order_chat}}', [
            'id' => Schema::TYPE_PK,
            'order_id' => Schema::TYPE_INTEGER  . ' not null',
            'sent_by_id' => Schema::TYPE_INTEGER  . ' not null',
            'is_system' => Schema::TYPE_INTEGER  . ' not null default 0',
            'message' => Schema::TYPE_STRING  . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->addForeignKey('{{%related_order}}', '{{%order_chat}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('{{%sent_by}}', '{{%order_chat}}', 'sent_by_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%sent_by}}', '{{%order_chat}}');
        $this->dropForeignKey('{{%related_order}}', '{{%order_chat}}');
        $this->dropTable('{{%order_chat}}');
    }
}
