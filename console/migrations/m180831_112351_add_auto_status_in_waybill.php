<?php

use yii\db\Migration;

/**
 * Class m180831_112351_add_auto_status_in_waybill
 */
class m180831_112351_add_auto_status_in_waybill extends Migration
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
        $this->addColumn('{{%iiko_waybill}}', 'autostatus_id', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%rk_waybill}}', 'autostatus_id', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%one_s_waybill}}', 'autostatus_id', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_waybill}}', 'autostatus_id');
        $this->dropColumn('{{%rk_waybill}}', 'autostatus_id');
        $this->dropColumn('{{%one_s_waybill}}', 'autostatus_id');
    }

}
