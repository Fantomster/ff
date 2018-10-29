<?php

use yii\db\Migration;

/**
 * Class m181024_125101_add_date_to_waybill_content
 */
class m181024_125101_add_date_to_waybill_content extends Migration
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
        $this->addColumn('{{%waybill_content}}', 'created_at', $this->timestamp()->null());
        $this->addColumn('{{%waybill_content}}', 'updated_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181024_125101_add_date_to_waybill_content cannot be reverted.\n";
        return false;
    }
}
