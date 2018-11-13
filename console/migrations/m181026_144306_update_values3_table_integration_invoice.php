<?php

use yii\db\Migration;

class m181026_144306_update_values3_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $sql = "SELECT `id`,`created_at`,`date` FROM `integration_invoice` WHERE `date` IS NULL OR `date`<'2017-01-01 00:00:00'";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $id = $row['id'];
            $date = $row['created_at'];
            $sql3 = "UPDATE `integration_invoice` SET `date`='".$date."' WHERE `id`=".$id;
            $result = Yii::$app->db->createCommand($sql3)->execute();
        }
    }

    public function safeDown()
    {
        echo "m181026_144306_update_values3_table_integration_invoice cannot be reverted.\n";
        return false;
    }
}
