<?php

use yii\db\Migration;

/**
 * Class m181020_081927_rename_column_unit_rid_and_store_rid_all_map_table
 */
class m181020_081927_rename_column_unit_rid_and_store_rid_all_map_table extends Migration
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
        $this->renameColumn('{{%all_map}}', 'unit_rid', 'outer_unit_id');
        $this->renameColumn('{{%all_map}}', 'store_rid', 'outer_store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181020_081927_rename_column_unit_rid_and_store_rid_all_map_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181020_081927_rename_column_unit_rid_and_store_rid_all_map_table cannot be reverted.\n";

        return false;
    }
    */
}
