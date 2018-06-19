<?php

use yii\db\Migration;

/**
 * Class m180619_093021_iiko_dic_default_status
 */
class m180619_093021_iiko_dic_default_status extends Migration
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
        $this->alterColumn('{{%iiko_dic}}', 'dicstatus_id', $this->integer(2)->defaultValue(3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%iiko_dic}}', 'dicstatus_id', $this->integer(11));
    }
}
