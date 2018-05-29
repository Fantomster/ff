<?php

use yii\db\Migration;

/**
 * Class m180523_113909_web_api_log_create
 */
class m180523_113909_web_api_log_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%web_api_log}}',[
            'id' => $this->primaryKey(),
            'url' => $this->string(),
            'request' => $this->text(),
            'response' => $this->text(),
            'user_id' => $this->integer(),
            'organization_id' => $this->integer(),
            'type' => $this->string()->defaultValue('success'),
            'request_at' => $this->timestamp(),
            'response_at' => $this->timestamp(),
            'guide' => $this->string(32)->notNull(),
            'ip' => $this->string(),
        ]);

        $this->createIndex('web_api_index_guide', 'web_api_log', 'guide', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('web_api_index_guide', 'web_api_log');
        $this->dropTable('{{%web_api_log}}');
    }
}
