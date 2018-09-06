<?php

use yii\db\Migration;

/**
 * Class m180904_155016_add_column_in_rabbit_queue
 */
class m180904_155016_add_column_in_rabbit_queue extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%rabbit_queues}}', 'data_request', $this->text());
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'data_request','Последние данные запроса');

    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rabbit_queues}}', 'data_request');

        return false;
    }
}
