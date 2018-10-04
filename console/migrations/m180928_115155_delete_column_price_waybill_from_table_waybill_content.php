<?php

use yii\db\Migration;

class m180928_115155_delete_column_price_waybill_from_table_waybill_content extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropColumn('waybill_content', 'price_waybill');
    }

    public function safeDown()
    {
        $this->addColumn('waybill_content', 'price_waybill', $this->float()->default(null));
    }
}
