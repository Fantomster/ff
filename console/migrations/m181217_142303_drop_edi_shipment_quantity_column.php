<?php

use yii\db\Migration;

/**
 * Class m181217_142303_drop_edi_shipment_quantity_column
 */
class m181217_142303_drop_edi_shipment_quantity_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%order_content}}', 'edi_shipment_quantity');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181217_142303_drop_edi_shipment_quantity_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181217_142303_drop_edi_shipment_quantity_column cannot be reverted.\n";

        return false;
    }
    */
}
