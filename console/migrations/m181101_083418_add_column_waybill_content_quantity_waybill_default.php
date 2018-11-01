<?php

use yii\db\Migration;

/**
 * Class m181101_083418_add_column_waybill_content_quantity_waybill_default
 */
class m181101_083418_add_column_waybill_content_quantity_waybill_default extends Migration
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
        $this->addColumn('waybill_content', 'quantity_waybill_default', $this->float()->null());
        $sql = "UPDATE waybill_content SET quantity_waybill_default = quantity_waybill";
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('waybill_content', 'quantity_waybill_default');
    }
}
