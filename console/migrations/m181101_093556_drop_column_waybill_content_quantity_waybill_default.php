<?php

use yii\db\Migration;

/**
 * Class m181101_093556_drop_column_waybill_content_quantity_waybill_default
 */
class m181101_093556_drop_column_waybill_content_quantity_waybill_default extends Migration
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
        $this->dropColumn('waybill_content', 'quantity_waybill_default');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
