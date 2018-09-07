<?php

use yii\db\Migration;

/**
 * Class m180906_130149_add_column_store_id_to_rabbit_queues_table
 */
class m180906_130149_add_column_store_id_to_rabbit_queues_table extends Migration
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
        $this->addColumn('{{%rabbit_queues}}', 'store_id', $this->integer()->null());
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'store_id','ID склада');
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'consumer_class_name','Класс консюмера');
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'organization_id','ID организации');
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'last_executed','Время последнего выполнения');
        $this->addCommentOnColumn('{{%rabbit_queues}}', 'start_executing','Время начала выполнения');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rabbit_queues}}', 'store_id');
    }
}
