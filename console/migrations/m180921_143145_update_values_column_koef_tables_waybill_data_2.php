<?php

use yii\db\Migration;

class m180921_143145_update_values_column_koef_tables_waybill_data_2 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('update `iiko_waybill_data` set `koef`=1 where `koef`=0');
        $this->execute('update `rk_waybill_data` set `koef`=1 where `koef`=0');
        $this->execute('update `one_s_waybill_data` set `koef`=1 where `koef`=0');
        $this->execute('update `all_map` set `koef`=1 where `koef`=0');
    }

    public function safeDown()
    {
        echo "m180921_134121_update_values_column_koef_tables_waybill_data cannot be reverted.\n";
        return false;
    }
}
