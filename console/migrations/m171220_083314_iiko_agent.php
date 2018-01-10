<?php

use yii\db\Migration;

/**
 * Class m171220_083314_iiko_agent
 */
class m171220_083314_iiko_agent extends Migration
{
    public $tableName = '{{%iiko_agent}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(36)->notNull(),
            'org_id' => $this->integer()->notNull(),
            'denom' => $this->string(250),
            'is_active' => $this->integer(1)->defaultValue(1),
            'comment' => $this->string(250)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
