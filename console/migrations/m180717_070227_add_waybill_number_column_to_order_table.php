<?php

use yii\db\Migration;

/**
 * Handles adding waybill_number to table `order`.
 */
class m180717_070227_add_waybill_number_column_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'waybill_number', $this->string(32)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'waybill_number');
    }
}
