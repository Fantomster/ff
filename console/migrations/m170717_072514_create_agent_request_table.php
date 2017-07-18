<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `agent_request`.
 */
class m170717_072514_create_agent_request_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%agent_request}}', [
            'id' => Schema::TYPE_PK,
            'agent_id' => Schema::TYPE_INTEGER . ' not null',
            'target_email' => Schema::TYPE_STRING . ' not null',
            'comment' => Schema::TYPE_STRING . ' null',
            'is_processed' => Schema::TYPE_BOOLEAN . ' not null default 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);

        $this->addForeignKey('{{%fk_agent_request}}', '{{%agent_request}}', 'agent_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_agent_request}}', '{{%agent_request}}');
        $this->dropTable('{{%agent_request}}');
    }
}
