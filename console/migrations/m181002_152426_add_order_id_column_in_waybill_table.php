<?php

use yii\db\Migration;

/**
 * Class m181002_152426_add_order_id_column_in_waybill_table
 */
class m181002_152426_add_order_id_column_in_waybill_table extends Migration
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
        $this->addColumn('{{%waybill}}', 'order_id', $this->integer()->null());
        $this->addCommentOnColumn('{{%waybill}}', 'order_id', 'ID заказа');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%waybill}}', 'order_id');
    }
}
