<?php

use yii\db\Migration;

/**
 * Class m180705_133155_add_is_category_column_in_one_s_good_table
 */
class m180705_133155_add_is_category_column_in_one_s_good_table extends Migration
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
        $this->addColumn('one_s_good', 'is_category', $this->boolean()->defaultValue(0));
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('one_s_good', 'is_category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180705_133155_add_is_category_column_in_one_s_good_table cannot be reverted.\n";

        return false;
    }
    */
}
