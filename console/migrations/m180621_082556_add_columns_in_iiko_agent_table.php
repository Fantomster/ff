<?php

use yii\db\Migration;

/**
 * Class m180621_082556_add_columns_in_iiko_agent_table
 */
class m180621_082556_add_columns_in_iiko_agent_table extends Migration
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
        $this->addColumn('{{%iiko_agent}}', 'vendor_id', $this->integer());
        $this->addColumn('{{%iiko_agent}}', 'store_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_agent}}', 'vendor_id');
        $this->dropColumn('{{%iiko_agent}}', 'store_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180621_082556_add_columns_in_iiko_agent_table cannot be reverted.\n";

        return false;
    }
    */
}
