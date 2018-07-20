<?php

use yii\db\Migration;

/**
 * Class m180705_084642_iiko_log
 */
class m180705_084642_iiko_log extends Migration
{
    public $tableName = '{{%iiko_log}}';

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
        $this->createTable($this->tableName,[
            'id' => $this->primaryKey(),
            'operation_code' => $this->string(25),
            'request' => $this->text(),
            'response' => $this->text(),
            'user_id' => $this->integer(),
            'organization_id' => $this->integer(),
            'type' => $this->string()->defaultValue('success'),
            'request_at' => $this->timestamp()->defaultValue(NULL),
            'response_at' => $this->timestamp()->defaultValue(NULL),
            'guide' => $this->string(32)->notNull(),
            'ip' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
