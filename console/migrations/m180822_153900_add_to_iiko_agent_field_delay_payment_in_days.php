<?php

use yii\db\Migration;

/**
 * Class m180822_153900_add_to_iiko_agent_field_delay_payment_in_days
 */
class m180822_153900_add_to_iiko_agent_field_delay_payment_in_days extends Migration
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
        $this->addColumn('{{%iiko_agent}}', 'payment_delay', $this->integer()->notNull()->defaultValue(0));
        $this->addCommentOnColumn('{{%iiko_agent}}', 'payment_delay',
            'Отсрочка платежа по договорам между контрагентами [тип: число, по умолчанию: 0, NOT NULL]');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_agent}}', 'payment_delay');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'payment_delay');
    }

}
