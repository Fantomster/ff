<?php

use yii\db\Migration;

/**
 * Class m180830_093326_iiko_product_index
 */
class m180830_093326_iiko_product_index extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    public $tableName = '{{%iiko_product}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('iiko_product_index_uuid', $this->tableName, 'uuid');
        $this->createIndex('iiko_product_index_org_id', $this->tableName, 'org_id');
        $this->createIndex('iiko_product_index_uuid_org_id', $this->tableName, ['uuid', 'org_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('iiko_product_index_uuid', $this->tableName);
        $this->dropIndex('iiko_product_index_org_id', $this->tableName);
        $this->dropIndex('iiko_product_index_uuid_org_id', $this->tableName);
    }
}
