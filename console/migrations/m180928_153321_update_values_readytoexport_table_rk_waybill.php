<?php

use yii\db\Migration;

class m180928_153321_update_values_readytoexport_table_rk_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('update `rk_waybill` set `readytoexport` = 0 where `readytoexport` is null');
    }

    public function safeDown()
    {
        echo "m180928_153321_update_values_readytoexport_table_rk_waybill cannot be reverted.\n";
        return false;
    }
}
