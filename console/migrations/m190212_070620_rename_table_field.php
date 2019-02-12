<?php

use yii\db\Migration;

/**
 * Class m190212_070620_rename_table_field
 */
class m190212_070620_rename_table_field extends Migration
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
        $this->renameColumn('{{%vetis_transport}}', 'trasport_storage_type', 'transport_storage_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190212_070620_rename_table_field cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190212_070620_rename_table_field cannot be reverted.\n";

        return false;
    }
    */
}
