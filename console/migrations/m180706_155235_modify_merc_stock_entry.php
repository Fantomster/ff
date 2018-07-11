<?php

use yii\db\Migration;

/**
 * Class m180706_155235_modify_merc_stock_entry
 */
class m180706_155235_modify_merc_stock_entry extends Migration
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
        $this->addColumn('merc_stock_entry', 'product_marks', $this->string(255)->defaultValue(null));
        $this->addColumn('merc_stock_entry', 'producer_country', $this->string(255)->defaultValue(null));
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('merc_stock_entry', 'product_marks');
        $this->dropColumn('merc_stock_entry', 'producer_country');
    }
}
