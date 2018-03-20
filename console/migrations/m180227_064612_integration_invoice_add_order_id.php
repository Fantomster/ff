<?php

use yii\db\Migration;

/**
 * Class m180227_064612_integration_invoice_add_order_id
 */
class m180227_064612_integration_invoice_add_order_id extends Migration
{
    public $tableName = 'integration_invoice';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'order_id', $this->integer()->after('email_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'order_id');
    }
}
