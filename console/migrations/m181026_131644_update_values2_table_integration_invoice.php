<?php

use yii\db\Migration;

class m181026_131644_update_values2_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $sql = "SELECT `id`,`created_at`,`date` FROM `integration_invoice` WHERE `date` IS NULL";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $id = $row['id'];
            $date = $row['created_at'];
            $this->update('{{%integration_invoice}}', ['date' => $date], ['id' =>$id]);
        }
        $sql2 = "SELECT `id`,`created_at`,`date` FROM `integration_invoice` WHERE `date` < '2017-01-01 00:00:00'";
        $rows2 = Yii::$app->db->createCommand($sql2)->queryAll();
        foreach ($rows2 as $row) {
            $id = $row['id'];
            $date = $row['created_at'];
            $this->update('{{%integration_invoice}}', ['date' => $date], ['id' =>$id]);
        }
    }

    public function safeDown()
    {
        echo "m181026_131644_update_values2_table_integration_invoice cannot be reverted.\n";
        return false;
    }
}
