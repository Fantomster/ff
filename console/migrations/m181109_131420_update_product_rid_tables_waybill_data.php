<?php

use yii\db\Migration;

class m181109_131420_update_product_rid_tables_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%rk_waybill_data}}', [
            'product_rid' => null],
            'product_rid=0'
        );
        $this->update('{{%iiko_waybill_data}}', [
            'product_rid' => null],
            'product_rid=0'
        );
        $this->update('{{%one_s_waybill_data}}', [
            'product_rid' => null],
            'product_rid=0'
        );
    }

    public function safeDown()
    {
        echo "m181109_131420_update_product_rid_tables_waybill_data cannot be reverted.\n";
        return false;
    }
}
