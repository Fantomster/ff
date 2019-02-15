<?php

use yii\db\Migration;

/**
 * Class m190215_072607_change_barcode_field_at_cbg_table
 */
class m190215_072607_change_barcode_field_at_cbg_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'barcode', $this->string(30));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%catalog_base_goods}}', 'barcode', $this->bigInteger(13));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190215_072607_change_barcode_field_at_cbg_table cannot be reverted.\n";

        return false;
    }
    */
}
