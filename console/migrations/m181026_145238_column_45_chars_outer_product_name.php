<?php

use yii\db\Migration;

/**
 * Class m181026_145238_column_45_chars_outer_product_name
 */
class m181026_145238_column_45_chars_outer_product_name extends Migration
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
        $this->alterColumn('outer_product', 'name', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181026_145238_column_45_chars_outer_product_name cannot be reverted.\n";
        return false;
    }
}
