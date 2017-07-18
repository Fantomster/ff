<?php

use yii\db\Migration;

/**
 * Handles the creation of table `agent_attachment`.
 */
class m170717_080916_create_agent_attachment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('agent_attachment', [
            'id' => $this->primaryKey(),
            'agent_request_id' => $this->integer()->notNull(),
            'attachment' => $this->string()->null(),
        ]);
        
        $this->addForeignKey('{{%fk_agent_attachment}}', '{{%agent_attachment}}', 'agent_request_id', '{{%agent_request}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('{{%fk_agent_attachment}}', '{{%agent_attachment}}');
        $this->dropTable('agent_attachment');
    }
}
