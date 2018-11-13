<?php

use yii\db\Migration;

/**
 * Class m181016_094442_add_last_error_column_for_merc_vsd_table
 */
class m181016_094442_add_last_error_column_for_merc_vsd_table extends Migration
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
        $this->addColumn('{{%merc_vsd}}', 'last_error', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%merc_vsd}}', 'last_error');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181016_094442_add_last_error_column_for_merc_vsd_table cannot be reverted.\n";

        return false;
    }
    */
}
