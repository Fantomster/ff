<?php

use yii\db\Migration;

/**
 * Class m180511_194101_add_linked_to_iiko_waybill
 */
class m180511_194101_add_linked_to_iiko_waybill extends Migration
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
        $this->addColumn('{{%iiko_waybill_data}}', 'linked_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_waybill_data}}', 'linked_at');
    }


}
