<?php

use yii\db\Migration;

class m180830_093522_delete_foreign_key_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk-integration_invoice_head-setting_from_email_id', 'integration_invoice');
    }

    public function safeDown()
    {
        echo "m180830_093522_delete_foreign_key_table_integration_invoice cannot be reverted.\n";
        return false;
    }
}
