<?php

use yii\db\Migration;

class m180917_072709_move_fields_to_other_tables extends Migration
{

    public function safeUp()
    {

        $this->dropColumn('{{%order_content}}', 'edi_ordersp');
        $this->addColumn('{{%order}}', 'edi_ordersp', $this->string(45)->defaultValue(null));
        $this->addCommentOnColumn('{{%order}}', 'edi_ordersp', 'Имя файла ORDERSP который прилетает от поставщик');

        $this->dropColumn('{{%waybill_content}}', 'edi_desadv');
        $this->addColumn('{{%order_content}}', 'edi_desadv', $this->string(45)->defaultValue(null));
        // $this->addCommentOnColumn('{{%order_content}}', 'edi_desadv', '');
        $this->dropColumn('{{%waybill_content}}', 'edi_alcdes');
        $this->addColumn('{{%order_content}}', 'edi_alcdes', $this->string(45)->defaultValue(null));
        // $this->addCommentOnColumn('{{%order_content}}', 'edi_alcdes', '');

        $this->dropColumn('{{%waybill}}', 'edi_number');
        $this->addColumn('{{%order_content}}', 'edi_number', $this->string(45)->defaultValue(null));
        // $this->addCommentOnColumn('{{%order_content}}', 'edi_number', '');
        $this->dropColumn('{{%waybill}}', 'edi_recadv');
        $this->addColumn('{{%order_content}}', 'edi_recadv', $this->string(45)->defaultValue(null));
        // $this->addCommentOnColumn('{{%order_content}}', 'edi_recadv', '');
        $this->dropColumn('{{%waybill}}', 'edi_invoice');
        $this->addColumn('{{%order_content}}', 'edi_invoice', $this->string(45)->defaultValue(null));
        // $this->addCommentOnColumn('{{%order_content}}', 'edi_invoice', '');

    }

    public function safeDown()
    {
    }

}
