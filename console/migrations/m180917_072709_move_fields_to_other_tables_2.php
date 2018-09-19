<?php

use yii\db\Migration;

class m180917_072709_move_fields_to_other_tables_2 extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropColumn('{{%waybill_content}}', 'edi_desadv');
        $this->dropColumn('{{%waybill_content}}', 'edi_alcdes');

        $this->dropColumn('{{%waybill}}', 'edi_number');
        $this->dropColumn('{{%waybill}}', 'edi_recadv');
        $this->dropColumn('{{%waybill}}', 'edi_invoice');
    }

    public function safeDown()
    {
    }

}
