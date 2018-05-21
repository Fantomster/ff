<?php

use yii\db\Migration;

/**
 * Class m180518_073431_alter_barcode_column_in_catalog_base_goods_table
 */
class m180518_073431_alter_barcode_column_in_catalog_base_goods_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'barcode', $this->bigInteger(13));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods', 'barcode');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180518_073431_alter_barcode_column_in_catalog_base_goods_table cannot be reverted.\n";

        return false;
    }
    */
}
