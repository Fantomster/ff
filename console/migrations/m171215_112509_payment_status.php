<?php

use yii\db\Migration;

/**
 * Class m171215_112509_payment_status
 */
class m171215_112509_payment_status extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('payment', 'status', $this->integer(1)->null()->defaultValue(2));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('payment', 'status');
    }
}
