<?php

use yii\db\Migration;

/**
 * Class m181024_152943_add_index_to_waybill_content
 */
class m181024_152943_add_index_to_waybill_content extends Migration
{
    public $table = 'waybill_content';

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
        $this->createIndex('idx_oc_' . $this->table, "{{%" . $this->table . "}}", 'order_content_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_oc_' . $this->table, "{{%" . $this->table . "}}");
    }
}
