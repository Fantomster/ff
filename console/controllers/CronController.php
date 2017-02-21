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
                ->andWhere('category_id is not null')
                ->andWhere(['in','es_status',[1,2]])
                ->limit(500)
                ->all();
        
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
                $product_partnership = $catalogBaseGoods->vendor->partnership; 
                
                $rating = $catalogBaseGoods->vendor->rating;
                if($product_image){$rating = $rating + 5;}
                if($product_show_price){$rating = $rating + 5;}
                
                
                if($catalogBaseGoods->es_status == 1 && $catalogBaseGoods->market_place == 1 && $catalogBaseGoods->deleted = 0){

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
                                "product_created_at"  => $product_created_at,
                                "product_rating"  => $rating,
                                "product_partnership"  => $product_partnership
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
                                "product_created_at"  => $product_created_at,
                                "product_rating"  => $rating,
                                "product_partnership"  => $product_partnership
                                        ];
                                $es_product->save();

                        }
                }
                if($catalogBaseGoods->es_status == 2 || $catalogBaseGoods->market_place == 0 || $catalogBaseGoods->deleted = 1){
                        if(\common\models\ES\Product::find()->where(['product_id' => $product_id])->count() > 0 ){
                                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
                                $es_product->delete();
                        }
                }
            
            Yii::$app->db->createCommand("update ".CatalogBaseGoods::tableName()." set "
                    . "es_status = 0, "
                    . "rating = " . $rating . " "
                    . "where id = " . $product_id)->execute();
        }
        
    }
    
    public function actionUpdateSuppliers() {
        $suppliers = Organization::find()
                ->where(['type_id'=>  Organization::TYPE_SUPPLIER])
                ->andWhere(['in','es_status',[
                    Organization::ES_UPDATED,
                    Organization::ES_DELETED
                    ]])
                ->limit(200)
                ->all();
        foreach($suppliers as $supplier){
            $rating = 0;
            if($supplier->partnership){$rating = $rating + 16;}
            if($supplier->picture){$rating = $rating + 5;} 
            if($supplier->contact_name){$rating = $rating + 2;} 
            if($supplier->phone){$rating = $rating + 2;} 
            if($supplier->email){$rating = $rating + 2;} 
            if($supplier->address){$rating = $rating + 2;} 
            if($supplier->about){$rating = $rating + 2;}
            
            if($supplier->es_status == Organization::ES_UPDATED){
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() == 0){
                    $es_supplier = new \common\models\ES\Supplier();
                    $es_supplier->attributes = [
                           "supplier_id" => $supplier->id,
                           "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                           "supplier_name"  => $supplier->name,
                           "supplier_rating"  => $rating,
                           "supplier_partnership"  => $supplier->partnership
                    ];
                    $es_supplier->save();
                }
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() > 0){
                    $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->one();
                    $es_supplier->attributes = [
                           "supplier_image" => !empty($supplier->picture) ? $supplier->pictureUrl : '',
                           "supplier_name"  => $supplier->name,
                           "supplier_rating"  => $rating,
                           "supplier_partnership"  => $supplier->partnership
                    ];
                    $es_supplier->save();  
                }
            }
            if($supplier->es_status == Organization::ES_DELETED){
                if(\common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->count() > 0){
                    $es_product = \common\models\ES\Supplier::find()->where(['supplier_id'=>$product_id])->one();
                    $es_product->delete();
                }
            }
            Yii::$app->db->createCommand("update organization set "
                    . "es_status = ".Organization::ES_INACTIVE.","
                    . "rating = ".$rating." "
                    . "where id = " . $supplier->id);
            if($supplier->white_list){
            Yii::$app->db->createCommand("update ".CatalogBaseGoods::tableName()." set "
                    . "es_status = ".CatalogBaseGoods::ES_UPDATE.", "
                    . "where supp_org_id = " . $supplier->id);
            }
        }
       
    }
    
    public function actionUpdateOrganizationRating() {
        
        
    }
    
    public function actionUpdateProductRating() {
        
        
    }
}
