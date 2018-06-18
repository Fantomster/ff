<?php

use yii\db\Migration;

/**
 * Class m180607_123902_alter_odinsrest_table_name
 */
class m180607_123902_alter_odinsrest_table_name extends Migration
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
        $this->execute('RENAME TABLE `odinsrest_access` TO `one_s_rest_access`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180607_123902_alter_odinsrest_table_name cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180607_123902_alter_odinsrest_table_name cannot be reverted.\n";

        return false;
    }
    */
}
