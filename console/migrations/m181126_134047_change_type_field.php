<?php

use yii\db\Migration;

/**
 * Class m181126_134047_change_type_field
 */
class m181126_134047_change_type_field extends Migration
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
        $this->alterColumn('waybill', 'doc_date', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
