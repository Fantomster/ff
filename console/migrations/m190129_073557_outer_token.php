<?php

use yii\db\Migration;

/**
 * Class m190129_073557_outer_token
 */
class m190129_073557_outer_token extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    private $tableName = '{{%outer_token}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id'              => $this->primaryKey(),
            'service_id'      => $this->integer()->notNull(),
            'organization_id' => $this->integer(),
            'token'           => $this->string(550)->notNull(),
            'created_at'      => $this->timestamp()
        ]);

        $this->createIndex('idx-service_id', $this->tableName, 'service_id');
        $this->createIndex('idx-service_id_org_id', $this->tableName, ['service_id','organization_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
