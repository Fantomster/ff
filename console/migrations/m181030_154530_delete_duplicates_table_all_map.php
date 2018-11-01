<?php

use yii\db\Migration;

class m181030_154530_delete_duplicates_table_all_map extends Migration
{
    public function safeUp()
    {
        $sql = "select `product_id`,`service_id`,`org_id` from `all_map` group by `product_id`,`service_id`,`org_id` HAVING count(`product_id`)>1 order by `product_id`,`org_id`,`service_id`";
        $rows = Yii::$app->db_api->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $product_id = $row['product_id'];
            $service_id = $row['service_id'];
            $org_id = $row['org_id'];
            $sql = "SELECT `id` FROM `all_map` WHERE `product_id`=".$product_id." AND `service_id`=".$service_id." AND `org_id`=".$org_id." ORDER BY `updated_at` DESC";
            $id_ver = Yii::$app->db_api->createCommand($sql)->queryScalar();
            $sql3 = "DELETE FROM `all_map` WHERE `product_id`=".$product_id." AND `service_id`=".$service_id." AND `org_id`=".$org_id." AND `id`<>".$id_ver;
            $result = Yii::$app->db_api->createCommand($sql3)->execute();
        }
    }

    public function safeDown()
    {
        echo "m181030_154530_delete_duplicates_table_all_map cannot be reverted.\n";
        return false;
    }
}
