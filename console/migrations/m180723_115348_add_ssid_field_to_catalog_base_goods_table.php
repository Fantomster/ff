<?php

use yii\db\Migration;

/**
 * Class m180723_115348_add_ssid_field_to_catalog_base_goods_table
 */
class m180723_115348_add_ssid_field_to_catalog_base_goods_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'ssid', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'ssid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180723_115348_add_ssid_field_to_catalog_base_goods_table cannot be reverted.\n";

        return false;
    }
    */
}
