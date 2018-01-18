<?php

use yii\db\Migration;

/**
 * Class m171220_082336_iiko_store
 */
class m171220_082336_iiko_store extends Migration
{
    public $tableName = '{{%iiko_store}}';

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
            'store_code' => $this->string(50)->null(),
            'store_type' => $this->string(100)->null(),
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
