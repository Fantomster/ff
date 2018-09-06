<?php

use yii\db\Migration;

/**
 * Class m180823_110000_add_to_iiko_waybill_field_delay_payment_in_days
 */
class m180827_136900_create_iiko_waybill_payment_delay_as_date extends Migration
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
        $this->addColumn('{{%iiko_waybill}}', 'payment_delay_date', $this->timestamp()->null()->defaultValue(null));
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'payment_delay_date',
            'Дата отсрочки платежа [тип: дата_и_время, по умолчанию: NULL]');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_waybill}}', 'payment_delay_date');
    }

}
