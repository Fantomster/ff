<?php

use yii\db\Migration;

/**
 * Handles adding vendor_id to table `rk_agent`.
 */
class m180823_102612_add_vendor_id_column_to_rk_agent_table extends Migration
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
        $this->addColumn('{{%rk_agent}}', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_agent}}}', 'vendor_id');
    }
}
