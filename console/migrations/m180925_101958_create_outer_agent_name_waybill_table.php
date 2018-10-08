<?php

use yii\db\Migration;

/**
 * Handles the creation of table `outer_agent_name_waybill`.
 */
class m180925_101958_create_outer_agent_name_waybill_table extends Migration
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
        $this->createTable('{{%outer_agent_name_waybill}}', [
            'id'       => $this->primaryKey(),
            'agent_id' => $this->integer()->comment('ID контрагента из outer_agent'),
            'name'     => $this->string()->comment('Название'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%outer_agent_name_waybill}}');
    }
}
