<?php

use yii\db\Migration;

class m180712_155355_add_column_consignee_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->addColumn('integration_invoice', 'consignee', $this->string()->null()->defaultValue(null));
        $this->addCommentOnColumn('{{%integration_invoice}}', 'consignee', 'Наименование грузополучателя, взятое из накладной ТОРГ-12');
    }

    public function safeDown()
    {
        $this->dropColumn('integration_invoice', 'consignee');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'consignee');
    }

}
