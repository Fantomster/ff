<?php

use yii\db\Migration;

/**
 * Class m180809_091853_update_iiko_pconst_status
 */
class m180809_091853_update_iiko_pconst_status extends Migration
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
        $this->update('iiko_dicconst', ['def_value' => 0, 'is_active' => 1], "denom='available_stores_list'");
        $this->update('iiko_dicconst', ['def_value' => 0, 'is_active' => 1], "denom='available_goods_list'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('iiko_dicconst', ['is_active' => 0], "denom='available_stores_list'");
        $this->update('iiko_dicconst', ['is_active' => 0], "denom='available_goods_list'");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180809_091853_update_iiko_pconst_status cannot be reverted.\n";

        return false;
    }
    */
}
