<?php

use yii\db\Migration;

/**
 * Class m180524_084910_alter_fields_in_order_table
 */
class m180524_084910_alter_fields_in_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'invoice_number', $this->string(32));
        $this->addColumn('order', 'invoice_date', $this->string(20));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'invoice_number');
        $this->dropColumn('order', 'invoice_date');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180524_084910_alter_fields_in_order_table cannot be reverted.\n";

        return false;
    }
    */
}
