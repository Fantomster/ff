<?php

use yii\db\Migration;

/**
 * Class m180713_134532_add_columns_to_api_for_one_s
 */
class m180713_134532_add_columns_to_api_for_one_s extends Migration
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
        $this->renameColumn('one_s_contragent', 'inn', 'inn_kpp');
        $this->addColumn('one_s_waybill', 'is_invoice', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('one_s_contragent', 'inn_kpp', 'inn');
        $this->dropColumn('one_s_waybill', 'is_invoice');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180713_134532_add_columns_to_api_for_one_s cannot be reverted.\n";

        return false;
    }
    */
}
