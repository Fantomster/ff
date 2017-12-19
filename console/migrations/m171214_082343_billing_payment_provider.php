<?php

use yii\db\Migration;

/**
 * Class m171214_082343_billing_payment_provider
 */
class m171214_082343_billing_payment_provider extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('billing_payment', 'provider', $this->string(255)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('billing_payment', 'provider');
    }
}
