<?php

use yii\db\Migration;

/**
 * Class m180307_114346_add_guides_from_test_vendors
 */
class m180307_114346_add_guides_from_test_vendors extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //        $vendor1 = Yii::$app->params['vendor1'];
//        $vendor2 = Yii::$app->params['vendor2'];
//        foreach ($vendor1['base_goods_ids'] as $good_id){
//            $good = \common\models\CatalogBaseGoods::findOne(['id'=>$good_id]);
//            if(!$good){
//                echo "Something went wrong. No good with id: ".$good_id;
//
//                return false;
//            }
//        }
//        foreach ($vendor2['base_goods_ids'] as $good_id){
//        $good = \common\models\CatalogBaseGoods::findOne(['id'=>$good_id]);
//        if(!$good){
//            echo "Something went wrong. No good with id: ".$good_id;
//
//            return false;
//        }
//    }
//
//        $vendorData1 = \common\models\Organization::findOne(['id'=>$vendor1['id']]);
//        if(!$vendorData1){
//            echo "Something went wrong. No vendor with id: ".$vendor1['id'];
//
//            return false;
//        }
//
//        $vendorData2 = \common\models\Organization::findOne(['id'=>$vendor2['id']]);
//        if(!$vendorData2){
//            echo "Something went wrong. No vendor with id: ".$vendor2['id'];
//
//            return false;
//        }
//
//        $catalogData1 = \common\models\Catalog::findOne(['id'=>$vendor1['catalog_id']]);
//        if(!$catalogData1){
//            echo "Something went wrong. No catalog with id: ".$vendor1['catalog_id'];
//
//            return false;
//        }
//
//        $catalogData2 = \common\models\Catalog::findOne(['id'=>$vendor2['catalog_id']]);
//        if(!$catalogData2){
//            echo "Something went wrong. No catalog with id: ".$vendor2['catalog_id'];
//
//            return false;
//        }
//        $colorsArray = [
//            'D81B60',
//            '8E24AA',
//            '5E35B1',
//            '5C6BC0',
//            '039BE5',
//            '009688',
//            'C0CA33',
//            'FFD600',
//            'FB8C00',
//            'F4511E',
//            'D32F2F',
//            'A1887F',
//            '5D4037',
//            'BDBDBD',
//            '757575',
//            '000000',
//        ];
//        $now = new \yii\db\Expression('NOW()');
//        $guide = [];
//        $guide_product = [];
//        $clients = \common\models\Organization::findAll(['type_id'=>1]);
//        foreach ($clients as $client){
//            $guides = \common\models\guides\Guide::findAll(['client_id'=>$client->id]);
//            if(!$guides){
//                $guide['client_id'] = $client->id;
//                $guide['type'] = 2;
//                $guide['name'] = 'Продукты на понедельник';
//                $guide['color'] = $colorsArray[array_rand($colorsArray)];
//                $guide['created_at'] = $now;
//                $guide['updated_at'] = $now;
//
//                $this->insert('guide', $guide);
//                $id = Yii::$app->db->getLastInsertID();
//                $this->insert('relation_supp_rest', ['rest_org_id'=>$client->id, 'supp_org_id'=>$vendor1['id'], 'cat_id'=>$vendor1['catalog_id'],
//                    'created_at'=>$now, 'updated_at'=>$now, 'status'=>1]);
//                foreach ($vendor1['base_goods_ids'] as $good_id) {
//                    $guide_product['guide_id'] = $id;
//                    $guide_product['cbg_id'] = $good_id;
//                    $guide_product['created_at'] = $now;
//                    $guide_product['updated_at'] = $now;
//                    $this->insert('guide_product', $guide_product);
//                }
//
//                $guide['name'] = 'Рыба для четверга';
//                $guide['color'] = $colorsArray[array_rand($colorsArray)];
//
//                $this->insert('guide', $guide);
//                $id = Yii::$app->db->getLastInsertID();
//                $this->insert('relation_supp_rest', ['rest_org_id'=>$client->id, 'supp_org_id'=>$vendor2['id'], 'cat_id'=>$vendor2['catalog_id'],
//                    'created_at'=>$now, 'updated_at'=>$now, 'status'=>1]);
//                foreach ($vendor2['base_goods_ids'] as $good_id) {
//                    $guide_product['guide_id'] = $id;
//                    $guide_product['cbg_id'] = $good_id;
//                    $guide_product['created_at'] = $now;
//                    $guide_product['updated_at'] = $now;
//                    $this->insert('guide_product', $guide_product);
//                }
//            }
//
//        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180307_114346_add_guides_from_test_vendors cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180307_114346_add_guides_from_test_vendors cannot be reverted.\n";

        return false;
    }
    */
}
