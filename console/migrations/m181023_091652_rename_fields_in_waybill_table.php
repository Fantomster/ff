<?php

use yii\db\Migration;

/**
 * Class m181023_091652_rename_fields_in_waybill_table
 */
class m181023_091652_rename_fields_in_waybill_table extends Migration
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
        $this->renameColumn('{{%waybill}}', 'outer_contractor_uuid', 'outer_agent_id');
        $this->renameColumn('{{%waybill}}', 'outer_store_uuid', 'outer_store_id');
        $this->alterColumn('{{%waybill}}', 'outer_agent_id', $this->integer()->null());
        $this->alterColumn('{{%waybill}}', 'outer_store_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181023_091652_rename_fields_in_waybill_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181023_091652_rename_fields_in_waybill_table cannot be reverted.\n";

        return false;
    }
    */
}
