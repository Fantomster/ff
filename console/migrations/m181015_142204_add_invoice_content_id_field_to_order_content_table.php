<?php

use yii\db\Migration;

/**
 * Class m181015_142204_add_invoice_content_id_field_to_order_content_table
 */
class m181015_142204_add_invoice_content_id_field_to_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_content}}', 'invoice_content_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_content}}', 'invoice_content_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181015_142204_add_invoice_content_id_field_to_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
