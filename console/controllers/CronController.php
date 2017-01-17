<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\CatalogBaseGoods;
//`php yii cron/count`
class CronController extends Controller {

    public function actionCount() {
        $restourants = rand(15, 25);
        $suppliers = rand(5, 10);
        $sql = "update main_counter set supp_count = supp_count + $suppliers, rest_count = rest_count + $restourants ";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionPlusOne() {
        $query = "SELECT updated_at FROM main_counter LIMIT 1";
        $latest = Yii::$app->db->createCommand($query)->queryScalar();
        $now = new \DateTime();
        $latest = new \DateTime($latest);
        $randomInterval = rand(3, 15);
        $interval = $now->diff($latest, true)->i;
        echo "latest:".Yii::$app->formatter->asTime($latest, "php:j M Y, H:i:s").";now:".Yii::$app->formatter->asTime($now, "php:j M Y, H:i:s").";diff:".$interval."\n";
    }
    
    public function actionSendMail() {
        Yii::$app->mailqueue->process();
    }
    //обновление одного продукта (крон запускается каждые 2 минуты)
    public function actionUpdateCollection() {
        if(CatalogBaseGoods::find()->where('es_status is not null')->exists()){
        //обновить / добавить
         if(CatalogBaseGoods::find()->where(['es_status' => '1'])->exists()){
            $products = CatalogBaseGoods::find()->where(['es_status' => '1'])->all(); 
            foreach($products as $catalogBaseGoods){
                $product_id = $catalogBaseGoods->id;
                $product_image = !empty($catalogBaseGoods->image) ? $catalogBaseGoods->imageUrl : ''; 
                $product_name = $catalogBaseGoods->product; 
                $product_supp_id = $catalogBaseGoods->supp_org_id;
                $product_supp_name = $catalogBaseGoods->vendor->name; 
                $product_price = $catalogBaseGoods->price; 
                $product_category_id = $catalogBaseGoods->category->parent; 
                $product_category_name = \common\models\MpCategory::find()->where(['id'=>$catalogBaseGoods->category->parent])->one()->name; 
                $product_category_sub_id = $catalogBaseGoods->category->id; 
                $product_category_sub_name = $catalogBaseGoods->category->name;
                $product_created_at = $catalogBaseGoods->created_at;
                
                if(\common\models\ES\Product::find()->where(['product_id' => $catalogBaseGoods->id])->exists()){
                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->one();
                $es_product->attributes = [
                    "product_id" => $product_id,
                    "product_image" => $product_image,
                    "product_name"  => $product_name,
                    "product_supp_id"  => $product_supp_id,
                    "product_supp_name"  => $product_supp_name,
                    "product_price"  => $product_price,
                    "product_category_id" => $product_category_id,
                    "product_category_name" => $product_category_name,
                    "product_category_sub_id" => $product_category_sub_id,
                    "product_category_sub_name" => $product_category_sub_name,
                    "product_created_at"  => $product_created_at
                ];
                $es_product->save();
                
                }else{
                $es_product = new \common\models\ES\Product();
                $es_product->attributes = [
                    "product_id" => $product_id,
                    "product_image" => $product_image,
                    "product_name"  => $product_name,
                    "product_supp_id"  => $product_supp_id,
                    "product_supp_name"  => $product_supp_name,
                    "product_price"  => $product_price,
                    "product_category_id" => $product_category_id,
                    "product_category_name" => $product_category_name,
                    "product_category_sub_id" => $product_category_sub_id,
                    "product_category_sub_name" => $product_category_sub_name,
                    "product_created_at"  => $product_created_at
                ];
                $es_product->save();
                
                }
            }
            CatalogBaseGoods::updateAll(['es_status' => 0], ['es_status' => 1]);
            $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
            $res = shell_exec($url);
         }
        //удалить
         if(CatalogBaseGoods::find()->where(['es_status' => '2'])->exists()){
            $products = CatalogBaseGoods::find()->where(['es_status' => '2'])->all();    
            foreach($products as $catalogBaseGoods){
             if(\common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->exists()){
             $es_product = \common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->one();
             $es_product->delete();    
             }    
            }
            CatalogBaseGoods::updateAll(['es_status' => 0], ['es_status' => 2]);
            $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
            $res = shell_exec($url);
         
         }
         
        }
        
    }
    // В случае, если обновление каталога было файлом
    // обновлять порциями, максимум 1000 строк
    // этот крон отрабатывается ночью с 00:00 по 05:00, каждые 10 минут
    // 0/10 00-05 * * * php yii cron/mass-update-collection
    public function actionMassUpdateCollection() {
        if(CatalogBaseGoods::find()->where('es_status is not null')->exists()){
            if(CatalogBaseGoods::find()->where(['es_status' => '3'])->exists()){
               $products = CatalogBaseGoods::find()->where(['es_status' => '3'])->limit(1000)->all();
               foreach($products as $catalogBaseGoods){
                $product_id = $catalogBaseGoods->id;
                $product_image = !empty($catalogBaseGoods->image) ? $catalogBaseGoods->imageUrl : ''; 
                $product_name = $catalogBaseGoods->product; 
                $product_supp_id = $catalogBaseGoods->supp_org_id;
                $product_supp_name = $catalogBaseGoods->vendor->name; 
                $product_price = $catalogBaseGoods->price; 
                $product_category_id = $catalogBaseGoods->category->parent; 
                $product_category_name = \common\models\MpCategory::find()->where(['id'=>$catalogBaseGoods->category->parent])->one()->name; 
                $product_category_sub_id = $catalogBaseGoods->category->id; 
                $product_category_sub_name = $catalogBaseGoods->category->name;
                $product_created_at = $catalogBaseGoods->created_at;
                
                if(\common\models\ES\Product::find()->where(['product_id' => $catalogBaseGoods->id])->exists()){
                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->one();
                $es_product->attributes = [
                    "product_id" => $product_id,
                    "product_image" => $product_image,
                    "product_name"  => $product_name,
                    "product_supp_id"  => $product_supp_id,
                    "product_supp_name"  => $product_supp_name,
                    "product_price"  => $product_price,
                    "product_category_id" => $product_category_id,
                    "product_category_name" => $product_category_name,
                    "product_category_sub_id" => $product_category_sub_id,
                    "product_category_sub_name" => $product_category_sub_name,
                    "product_created_at"  => $product_created_at
                ];
                $es_product->save();
                CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $catalogBaseGoods->id]);
                }else{
                $es_product = new \common\models\ES\Product();
                $es_product->attributes = [
                    "product_id" => $product_id,
                    "product_image" => $product_image,
                    "product_name"  => $product_name,
                    "product_supp_id"  => $product_supp_id,
                    "product_supp_name"  => $product_supp_name,
                    "product_price"  => $product_price,
                    "product_category_id" => $product_category_id,
                    "product_category_name" => $product_category_name,
                    "product_category_sub_id" => $product_category_sub_id,
                    "product_category_sub_name" => $product_category_sub_name,
                    "product_created_at"  => $product_created_at
                ];
                $es_product->save();
                CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $catalogBaseGoods->id]);    
                } 
               }
               
               $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
               $res = shell_exec($url);
            }
            if(CatalogBaseGoods::find()->where(['es_status' => '4'])->exists()){
               $products = CatalogBaseGoods::find()->where(['es_status' => '4'])->limit(1000)->all();
               foreach($products as $catalogBaseGoods){
                if(\common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->exists()){
                  $es_product = \common\models\ES\Product::find()->where(['product_id'=>$catalogBaseGoods->id])->one();
                  $es_product->delete();  
                  CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $catalogBaseGoods->id]);
                }
               } 
               
               $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
               $res = shell_exec($url);
            }
        }
    }
}
