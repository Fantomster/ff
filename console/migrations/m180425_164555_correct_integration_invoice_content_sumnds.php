<?php

use yii\db\Migration;

/**
 * Class m180425_164555_correct_integration_invoice_content_sumnds
 */
class m180425_164555_correct_integration_invoice_content_sumnds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%integration_invoice_content}}', 'sum_without_nds', $this->double(12,3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%integration_invoice_content}}', 'sum_without_nds');
    }


}
