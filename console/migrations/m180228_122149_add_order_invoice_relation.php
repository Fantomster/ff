<?php

use yii\db\Migration;

/**
 * Class m180228_122149_add_order_invoice_relation
 */
class m180228_122149_add_order_invoice_relation extends Migration
{
    public $tableName = 'order';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'invoice_relation', $this->integer()->after('total_price'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'invoice_relation');
    }
}
