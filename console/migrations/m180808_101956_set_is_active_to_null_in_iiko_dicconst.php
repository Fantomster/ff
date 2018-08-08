<?php

use yii\db\Migration;

/**
 * Class m180808_101956_set_is_active_to_null_in_iiko_dicconst
 */
class m180808_101956_set_is_active_to_null_in_iiko_dicconst extends Migration
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
        $this->update('iiko_dicconst', ['is_active' => 0], "denom='available_stores_list'");
        $this->update('iiko_dicconst', ['is_active' => 0], "denom='available_goods_list'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('iiko_dicconst', ['is_active' => 1], "denom='available_stores_list'");
        $this->update('iiko_dicconst', ['is_active' => 1], "denom='available_goods_list'");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180808_101956_set_is_active_to_null_in_iiko_dicconst cannot be reverted.\n";

        return false;
    }
    */
}
