<?php

use yii\db\Migration;

/**
 * Class m181101_091155_add_field_waybill_id_to_outer_task_table
 */
class m181101_091155_add_field_waybill_id_to_outer_task_table extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%outer_task}}', 'waybill_id', $this->integer()->null()->comment('Связь с накладной из waybill'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%outer_task}}', 'waybill_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181101_091155_add_field_waybill_id_to_outer_task_table cannot be reverted.\n";

        return false;
    }
    */
}
