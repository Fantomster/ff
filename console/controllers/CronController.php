<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\WhiteList;
use common\models\CatalogBaseGoods;
use common\models\Organization;
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
        $base = CatalogBaseGoods::find()
                //->select('catalog_base_goods.*')
                ->joinWith('whiteList')
                ->where(['market_place' => 1,'deleted'=>0])
                ->andWhere('category_id is not null')
                ->andWhere(['in','es_status',[1,2]])
                ->andWhere('organization_id is not null')
                ->limit(500)
                ->all();
        //var_dump($base->catalogBaseGoods);
        //foreach($base as $catalogBaseGoods){var_dump($catalogBaseGoods->whiteList->organization_id);}
        
        foreach($base as $catalogBaseGoods){
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
                $product_show_price = $catalogBaseGoods->mp_show_price;
                $product_created_at = $catalogBaseGoods->created_at;

                if($catalogBaseGoods->es_status == 1){

                        if(\common\models\ES\Product::find()->where(['product_id' => $product_id])->count() > 0 ){

                                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
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
                                "product_show_price" => $product_show_price,
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
                                "product_show_price" => $product_show_price,
                                "product_created_at"  => $product_created_at
                                        ];
                                        $es_product->save();

                        }
                }
                if($catalogBaseGoods->es_status == 2){

                        if(\common\models\ES\Product::find()->where(['product_id' => $product_id])->count() > 0 ){
                                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
                                $es_product->delete();
                        }
                }
            CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $product_id]);
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
                $product_show_price = $catalogBaseGoods->mp_show_price;
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
                    "product_show_price" => $product_show_price,
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
                    "product_show_price" => $product_show_price,
                    "product_created_at"  => $product_created_at
                ];
                $es_product->save();
                CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $catalogBaseGoods->id]);    
                } 
               }
               
               //$url = 'curl -XPOST \'http://' . Yii::$app->elasticsearch->nodes[0]['http_address'] . '/product/_refresh\'';
               //$res = shell_exec($url);
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
               
               $url = 'curl -XPOST \'http://' . Yii::$app->elasticsearch->nodes[0]['http_address'] . '/product/_refresh\'';
               $res = shell_exec($url);
            }
        }
    }
    public function actionUpdateSuppliers() {
       $suppliers = WhiteList::find()
                ->joinWith('organization')
                ->andWhere(['in','es_status',[
                    Organization::ES_ACTIVE,
                    Organization::ES_INACTIVE,
                    Organization::ES_UPDATED
                    ]])
                ->limit(200)
                ->all();
        foreach($suppliers as $supplier){
            if($suppliers->es_status == Organization::ES_ACTIVE){
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() == 0){
                    $es_supplier = new \common\models\ES\Supplier();
                    $es_supplier->attributes = [
                           "supplier_id" => $supplier->id,
                           "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                           "supplier_name"  => $supplier->name,
                    ];
                    $es_supplier->save();
                }
            }
            if($suppliers->es_status == Organization::ES_UPDATED){
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() == 0){
                    $es_supplier = new \common\models\ES\Supplier();
                    $es_supplier->attributes = [
                           "supplier_id" => $supplier->id,
                           "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                           "supplier_name"  => $supplier->name,
                    ];
                    $es_supplier->save();
                }
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() > 0){
                    $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->one();
                    $es_supplier->attributes = [
                           "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                           "supplier_name"  => $supplier->name,
                    ];
                    $es_supplier->save();  
                }
            }
            if($suppliers->es_status == Organization::ES_INACTIVE){
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() > 0){
                    $es_product = \common\models\ES\Supplier::find()->where(['supplier_id'=>$product_id])->one();
                    $es_product->delete();
                }
            }
            Yii::$app->db->createCommand("update organization set es_status = 1 where id = " . $supplier->id);
        }
       
    }
    public function actionMassUpdateSuppliers() {
        /*$suppliers_array = Yii::$app->db->createCommand("SELECT * from organization
        join (SELECT DISTINCT `supp_org_id` FROM `catalog_base_goods` WHERE (`market_place`=1) AND (`deleted`=0))tb
        on (id = tb.supp_org_id)")->queryAll();*/
        $suppliers = CatalogBaseGoods::find()
                ->select('supp_org_id')
                ->where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON,'deleted'=>CatalogBaseGoods::DELETED_OFF])
                ->distinct()
                ->all();
        $arr = [];
        foreach($suppliers as $supplier){
            $arr[] = $supplier->supp_org_id;
            
        }
        $arr = implode(',',$arr);
        if(!empty($arr)){
           Yii::$app->db->createCommand("update organization set es_status = 1 where id in (" . $arr . ")")->execute();  
           Yii::$app->db->createCommand("update organization set es_status = 0 where es_status is null")->execute();
        }
        $suppliers = Organization::findAll(['es_status'=>Organization::ES_ACTIVE]);
        foreach($suppliers as $supplier){
            if(!\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->exists()){
             $es_supplier = new \common\models\ES\Supplier();
             $es_supplier->attributes = [
                    "supplier_id" => $supplier->id,
                    "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                    "supplier_name"  => $supplier->name,
             ];
             $es_supplier->save();
            }else{
             $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->one();
             $es_supplier->attributes = [
                    "supplier_id" => $supplier->id,
                    "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                    "supplier_name"  => $supplier->name,
             ];
             $es_supplier->save();   
            }
        }
    }
}
