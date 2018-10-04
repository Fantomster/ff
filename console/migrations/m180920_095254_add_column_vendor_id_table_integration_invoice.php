<?php

use yii\db\Migration;

class m180920_095254_add_column_vendor_id_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%integration_invoice}}', 'vendor_id', $this->integer()->null());
        $this->addCommentOnColumn('{{%integration_invoice}}', 'vendor_id','Идентификатор организации-поставщика');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'vendor_id');
        $this->dropColumn('{{%integration_invoice}}', 'vendor_id');
        return false;
    }
}
