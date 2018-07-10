<?php

use yii\db\Migration;

/**
 * Class m180710_072241_add_columns_in_edi_order_content_table
 */
class m180710_072241_add_columns_in_edi_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('edi_order_content', 'uuid', $this->string(100)->null());
        $this->addColumn('edi_order_content', 'gtin', $this->string(100)->null());
        $this->addColumn('edi_order_content', 'waybill_date', $this->timestamp()->null());
        $this->addColumn('edi_order_content', 'waybill_number', $this->string(50)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('edi_order_content', 'uuid');
        $this->dropColumn('edi_order_content', 'gtin');
        $this->dropColumn('edi_order_content', 'waybill_date');
        $this->dropColumn('edi_order_content', 'waybill_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180710_072241_add_columns_in_edi_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
