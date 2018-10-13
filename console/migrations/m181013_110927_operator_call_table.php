<?php

use yii\db\Migration;

/**
 * Class m181013_110927_operator_call_table
 */
class m181013_110927_operator_call_table extends Migration
{
    public $tableName = '{{%operator_call}}';
    public $tableNameTimeout = '{{%operator_timeout}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'order_id' => $this->primaryKey(),
            'operator_id' => $this->integer()->notNull(),
            'status_call_id' => $this->tinyInteger()->null(),
            'comment' => $this->string()->null(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp()->null(),
            'closed_at' => $this->timestamp()->null(),
        ]);

        $this->createTable($this->tableNameTimeout, [
            'operator_id' => $this->integer()->notNull(),
            'timeout_at' => $this->timestamp(),
            'timeout' => $this->integer()->defaultValue(0)
        ]);

        $this->addColumn('order', 'accepted_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
        $this->dropTable($this->tableNameTimeout);
        $this->dropColumn('order', 'accepted_at');
    }
}
