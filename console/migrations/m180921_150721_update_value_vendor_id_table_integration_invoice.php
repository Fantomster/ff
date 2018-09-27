<?php

use yii\db\Migration;
use yii\db\Query;

class m180921_150721_update_value_vendor_id_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $query = new Query;
        $rows = $query->select('id,order_id')->from('integration_invoice')->all();
        foreach ($rows as $row) {
            $order_id = $row["order_id"];
            if ($order_id) {
                $id = $row["id"];
                $query2 = new Query;
                $rows2 = $query2->select('vendor_id')->from('order') ->where('id=:id',['id' => $order_id])->one();
                $vid = $rows2["vendor_id"];
                $this->update('{{%integration_invoice}}',
                    ['vendor_id' => $vid],
                    ['id' => $id]);
            }
        }
    }

    public function safeDown()
    {
        $this->execute('update `integration_invoice` set `vendor_id` IS NULL');
    }
}
