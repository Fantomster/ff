<?php

use yii\db\Migration;

/**
 * Class m180904_103510_add_new_statuses_to_waybills
 */
class m180904_103510_add_new_statuses_to_waybills extends Migration
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
        $this->insert('{{%iiko_waybill_status}}', ['id' => 5, 'denom' => 'Отклонена', 'comment' => '']);
        $this->insert('{{%rk_waybillstatus}}', ['id' => 6, 'denom' => 'Отклонена', 'comment' => '']);
        $this->insert('{{%one_s_waybill_status}}', ['id' => 4, 'denom' => 'Отклонена', 'comment' => '']);

        $this->insert('{{%iiko_waybill_status}}', ['id' => 6, 'denom' => 'Пересоздана', 'comment' => '']);
        $this->insert('{{%rk_waybillstatus}}', ['id' => 7, 'denom' => 'Пересоздана', 'comment' => '']);
        $this->insert('{{%one_s_waybill_status}}', ['id' => 5, 'denom' => 'Пересоздана', 'comment' => '']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%one_s_waybill_status}}', ['id' => 4]);
        $this->delete('{{%rk_waybillstatus}}', ['id' => 6]);
        $this->delete('{{%iiko_waybill_status}}', ['id' => 6]);

        $this->delete('{{%one_s_waybill_status}}', ['id' => 5]);
        $this->delete('{{%rk_waybillstatus}}', ['id' => 7]);
        $this->delete('{{%iiko_waybill_status}}', ['id' => 5]);
    }


}
