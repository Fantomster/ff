<?php

use yii\db\Migration;

/**
 * Class m180516_130316_mer_log_table_in_api
 */
class m180516_130316_mer_log_table_in_api extends Migration
{
    public $tableName = '{{%merc_log}}';

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
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(11),
                'action' => $this->string(255)->null()->defaultValue(null),
                'localTransactionId' => $this->string(100)->null()->defaultValue(null),
                'applicationId' => $this->string(255)->null()->defaultValue(null),
                'user_id' => $this->integer(11)->null()->defaultValue(null),
                'organization_id' => $this->integer(11)->null()->defaultValue(null),
                'status' => $this->string(45)->null()->defaultValue(null),
                'description' => $this->text()->null()->defaultValue(null),
                'created_at' => $this->datetime()->null()->defaultValue(null),
            ], $tableOptions
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
