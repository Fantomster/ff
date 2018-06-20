<?php

use yii\db\Migration;

/**
 * Class m180613_073746_add_columns_in_edi_order_content_table
 */
class m180613_073746_add_columns_in_edi_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('edi_order_content', 'barcode', $this->bigInteger(13)->null());
        $this->addColumn('edi_order_content', 'pricewithvat', $this->decimal(10, 2)->defaultValue(0.00));
        $this->addColumn('edi_order_content', 'taxrate', $this->decimal(5,2)->defaultValue(0.00));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('edi_order_content', 'barcode');
        $this->dropColumn('edi_order_content', 'pricewithvat');
        $this->dropColumn('edi_order_content', 'taxrate');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180613_073746_add_columns_in_edi_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
