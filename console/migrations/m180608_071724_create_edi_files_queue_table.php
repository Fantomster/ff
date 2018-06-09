<?php

use yii\db\Migration;

/**
 * Handles the creation of table `edi_files_queue`.
 */
class m180608_071724_create_edi_files_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('edi_files_queue', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'organization_id' => $this->integer()->notNull(),
            'status' => $this->integer(1)->defaultValue(1),
            'error_text' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('edi_files_queue');
    }
}
