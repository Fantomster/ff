<?php

use yii\db\Migration;

class m181030_100458_update_values_supp_id_table_all_map extends Migration
{
    public function safeUp()
    {
        $sql = "SELECT `id`,`product_id` FROM `all_map`";
        $rows = Yii::$app->db_api->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $id = $row['id'];
            $product_id = $row['product_id'];
            $sql = "SELECT `supp_org_id` FROM `catalog_base_goods` WHERE `id`=".$product_id;
            $supp = Yii::$app->db->createCommand($sql)->queryScalar();
            $sql3 = "UPDATE `all_map` SET `supp_id`='".$supp."' WHERE `id`=".$id;
            $result = Yii::$app->db_api->createCommand($sql3)->execute();
        }
    }

    public function safeDown()
    {
        echo "m181030_100458_update_values_supp_id_table_all_map cannot be reverted.\n";
        return false;
    }
}
