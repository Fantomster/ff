<?php

use yii\db\Migration;

/**
 * Class m180712_062840_journal
 */
class m180712_062840_journal extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%journal}}',[
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'operation_code' => $this->string()->notNull(),
            'user_id' => $this->integer(),
            'organization_id' => $this->integer(),
            'response' => $this->text(),
            'log_guide' => $this->string(),
            'type' => $this->string(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%journal}}');
    }
}
