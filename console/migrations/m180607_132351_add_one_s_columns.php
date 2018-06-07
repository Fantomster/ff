<?php

use yii\db\Migration;

/**
 * Class m180607_132351_add_one_s_columns
 */
class m180607_132351_add_one_s_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    public function safeUp()
    {
        $this->addColumn('one_s_contragent', 'org_id', $this->integer());
        $this->addColumn('one_s_store', 'org_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('one_s_contragent', 'org_id');
        $this->dropColumn('one_s_store', 'org_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180607_132351_add_one_s_columns cannot be reverted.\n";

        return false;
    }
    */
}
