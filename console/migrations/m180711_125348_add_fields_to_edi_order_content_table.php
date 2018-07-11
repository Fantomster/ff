<?php

use yii\db\Migration;

/**
 * Class m180711_125348_add_fields_to_edi_order_content_table
 */
class m180711_125348_add_fields_to_edi_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('edi_order_content', 'delivery_note_number', $this->string(100)->null());
        $this->addColumn('edi_order_content', 'delivery_note_date', $this->timestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('edi_order_content', 'delivery_note_number');
        $this->dropColumn('edi_order_content', 'delivery_note_date');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_125348_add_fields_to_edi_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
