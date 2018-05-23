<?php

use yii\db\Migration;

/**
 * Class m180522_151622_add_total_amount_tax_to_invoice
 */
class m180522_151622_add_total_amount_tax_to_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%integration_invoice}}', 'total_sum_withouttax', $this->decimal(15,2)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%integration_invoice}}', 'total_sum_withouttax');
    }

}
