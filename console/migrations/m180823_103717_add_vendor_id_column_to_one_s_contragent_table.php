<?php

use yii\db\Migration;

/**
 * Handles adding vendor_id to table `one_s_contragent`.
 */
class m180823_103717_add_vendor_id_column_to_one_s_contragent_table extends Migration
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
        $this->addColumn('{{%one_s_contragent}}', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%one_s_contragent}}}', 'vendor_id');
    }
}
