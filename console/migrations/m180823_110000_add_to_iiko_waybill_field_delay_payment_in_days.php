<?php

use yii\db\Migration;

/**
 * Class m180823_110000_add_to_iiko_waybill_field_delay_payment_in_days
 */
class m180823_110000_add_to_iiko_waybill_field_delay_payment_in_days extends Migration
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
        $this->addColumn('{{%iiko_waybill}}', 'payment_delay', $this->integer()->notNull()->defaultValue(0));
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'payment_delay',
            'Отсрочка платежа по данной накладной [тип: число, по умолчанию: 0, NOT NULL]');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_waybill}}', 'payment_delay');
    }

}
