<?php

use yii\db\Migration;

/**
 * Class m180716_135225_add_discount_columns_in_one_s_waybills_table
 */
class m180716_135225_add_discount_columns_in_one_s_waybills_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('one_s_waybill', 'discount', $this->decimal(10, 2)->null());
        $this->addColumn('one_s_waybill', 'discount_type', $this->integer(11)->null());
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('one_s_waybill', 'discount');
        $this->dropColumn('one_s_waybill', 'discount_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180716_135225_add_discount_columns_in_one_s_waybills_table cannot be reverted.\n";

        return false;
    }
    */
}
