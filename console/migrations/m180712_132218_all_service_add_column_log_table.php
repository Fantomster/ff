<?php

use yii\db\Migration;

/**
 * Class m180712_132218_all_service_add_column_log_table
 */
class m180712_132218_all_service_add_column_log_table extends Migration
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
        $this->addColumn('{{%all_service}}', 'log_table', $this->string());
        $this->update('{{%all_service}}', ['log_table' => 'iiko_log'], ['id' => 2]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%all_service}}', 'log_table');
    }
}
