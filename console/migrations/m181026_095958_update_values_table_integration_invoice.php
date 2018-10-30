<?php

use yii\db\Migration;
use yii\db\Query;

class m181026_095958_update_values_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $query = new Query;
        $query->select('id','created_at')->from('integration_invoice')->where('date!=:date',['date' => null]);
        $rows = $query->all();
        foreach ($rows as $row) {
            $id = $row->id;
            $date = $row->created_at;
            $this->update('{{%integration_invoice}}', ['date' => $date], ['id' =>$id]);
        }
        $query2 = new Query;
        $query2->select(['id','created_at'])->from('integration_invoice')->where('date<:date',['date' => '2017-01-01 00:00:00']);
        $rows = $query2->all();
        foreach ($rows as $row) {
            $id = $row['id'];
            $date = $row['created_at'];
            $this->update('{{%integration_invoice}}', ['date' => $date], ['id' =>$id]);
        }
    }

    public function safeDown()
    {
        echo "m181026_095958_update_values_table_integration_invoice cannot be reverted.\n";
        return false;
    }
}
