<?php

use yii\db\Migration;

/**
 * Class m180921_082331_iiko_dicconst_main_org_update_type
 */
class m180921_082331_iiko_dicconst_main_org_update_type extends Migration
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
        $this->update('{{%iiko_dicconst}}', ['type' => 4], "denom='main_org'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('{{%iiko_dicconst}}', ['type' => 1], "denom='main_org'");
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
