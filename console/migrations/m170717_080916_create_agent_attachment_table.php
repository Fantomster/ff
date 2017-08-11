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
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('agent_attachment', [
            'id' => $this->primaryKey(),
            'agent_request_id' => $this->integer()->notNull(),
            'attachment' => $this->string()->null(),
        ], $tableOptions);
        
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
