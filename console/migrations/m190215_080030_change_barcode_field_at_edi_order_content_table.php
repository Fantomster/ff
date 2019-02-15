<?php

use yii\db\Migration;

/**
 * Class m190215_080030_change_barcode_field_at_edi_order_content_table
 */
class m190215_080030_change_barcode_field_at_edi_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%edi_order_content}}', 'barcode', $this->string(30));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%edi_order_content}}', 'barcode', $this->bigInteger(13));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190215_080030_change_barcode_field_at_edi_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
