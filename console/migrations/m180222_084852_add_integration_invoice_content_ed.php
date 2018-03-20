<?php

use yii\db\Migration;

/**
 * Class m180222_084852_add_integration_invoice_content_ed
 */
class m180222_084852_add_integration_invoice_content_ed extends Migration
{
    public $tableName = 'integration_invoice_content';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'ed', $this->string()->after('quantity'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'ed');
    }
}
