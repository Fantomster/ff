<?php

namespace console\controllers;

use Yii;
use yii\web\View;
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
                
                $product_rating = $catalogBaseGoods->vendor->rating;
                if(!empty($product_image)){$product_rating = $product_rating + 5;}
                if(!empty($product_show_price)){$product_rating = $product_rating + 5;}
               
                if($catalogBaseGoods->es_status == 1 && $catalogBaseGoods->market_place == 1 && $catalogBaseGoods->deleted == 0){

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
                                "product_rating"  => $product_rating,
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
                                "product_rating"  => $product_rating,
                                "product_partnership"  => $product_partnership
                                        ];
                                $es_product->save();

                        }
                }else{
                if(\common\models\ES\Product::find()->where(['product_id' => $product_id])->count() > 0 ){
                    $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
                    $es_product->delete();
                } 
                /*if($catalogBaseGoods->es_status == 2 || $catalogBaseGoods->market_place == 0 || $catalogBaseGoods->deleted == 1){
                        if(\common\models\ES\Product::find()->where(['product_id' => $product_id])->count() > 0 ){
                                $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
                                $es_product->delete();
                        }*/
                }
            
            Yii::$app->db->createCommand("update ".CatalogBaseGoods::tableName()." set "
                    . "es_status = 0, "
                    . "rating = " . $product_rating . " "
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
                    $es_supplier = \common\models\ES\Supplier::find()->where(['supplier_id'=>$supplier->id])->one();
                    $es_supplier->delete();
                }
                Yii::$app->db->createCommand("update ".CatalogBaseGoods::tableName()." set "
                    . "es_status = ".Organization::ES_DELETED." "
                    . "where supp_org_id = " . $supplier->id)->execute();
                
            }
            Yii::$app->db->createCommand("update organization set "
                    . "es_status = ".Organization::ES_INACTIVE.","
                    . "rating = ".$rating." "
                    . "where id = " . $supplier->id)->execute();
            if($supplier->white_list == 1){
            Yii::$app->db->createCommand("update ".CatalogBaseGoods::tableName()." set "
                    . "es_status = ".CatalogBaseGoods::ES_UPDATE." "
                    . "where supp_org_id = " . $supplier->id . " and "
                    . "es_status <> " . CatalogBaseGoods::ES_DELETED)->execute();
            }
        }
       
    }
    
    public function actionUpdateOrganizationRating() {
        
        
    }
    
    public function actionUpdateProductRating() {
        
        
    }
    
    public function actionGeoFranchiseeAndOrganization() {
        $orgTable = Organization::tableName();
        $fraTable = \common\models\FranchiseeAssociate::tableName();
        if(Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where('(`country` is not null and `country` <>"undefined" and `country` <>"") 
    and locality <>"Москва" and `locality` <>"Московская область" and `franchisee_associate`.id is null')
                ->exists()){
	//берем в массив все актуальные организации но 500 штук
        $organizations = Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where('(`country` is not null and `country` <>"undefined" and `country` <>"") 
    and locality <>"Москва" and `locality` <>"Московская область" and `franchisee_associate`.id is null')
                ->limit(500)->all();

            foreach($organizations as $organization)
            {
                if(empty($organization->administrative_area_level_1) && !empty($organization->lat) && !empty($organization->lng)){
                    $address_url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.Yii::$app->params['google-api']['key-id'].'&latlng=' . $organization->lat . ',' . $organization->lng . '&language=ru&sensor=false';
                    $address_json = json_decode(file_get_contents($address_url));
                    if(!empty($address_json->results[0]->address_components)){
                        $address_data = $address_json->results[0]->address_components;
                        $location = array();
                        $location['locality'] = '';
                        $location['admin_1'] = '';
                        $location['country'] = '';
                            foreach ($address_data as $component) {
                              switch ($component->types) {
                                case in_array('locality', $component->types):
                                  $location['locality'] = $component->long_name;
                                  break;
                                case in_array('administrative_area_level_1', $component->types):
                                  $location['admin_1'] = $component->long_name;
                                  break;
                                case in_array('country', $component->types):
                                  $location['country'] = $component->long_name;
                                  break;
                              }

                            }  
                        $country = $location['country'];
                        $locality = $location['locality'];
                        $administrative_area_level_1 = $location['admin_1'];

                        $model = Organization::findOne($organization->id);
                        if(empty($model->locality) || $model->locality == 'undefined'){
                        $model->locality = $locality;    
                        }
                        $model->administrative_area_level_1 = $administrative_area_level_1;
                        $model->save();
                    }
                }
                //Есть ли франшиза с этой страной?
                if(\common\models\FranchiseeGeo::find()->where(['country'=>$organization->country])->exists()){
                    //если есть
                    //есть ли франшиза с этим городом?
                        $flag = 0;
                        if(\Yii::$app->db->createCommand("select count(*) from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where LENGTH(locality)>2 and country = '" . $organization->country . "' and 
                          locality = '" . $organization->locality . "' order by type_id desc")->queryScalar()>0){     
                    //Если есть, тогда дать список всех франшиз с этой городом
                    $pullFranchisees = \Yii::$app->db->createCommand("select * from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where LENGTH(locality)>2 and country = '" . $organization->country . "' and 
                          locality = '" . $organization->locality . "' order by type_id desc")->queryAll();
                         ;
                        self::setTypeFranchiseeAndSaveAssoc($pullFranchisees,$organization);
                        $flag = 1;
                    }
                    //А есть ли франшиза с этой областью? 
                    if($flag == 0 && \Yii::$app->db->createCommand("select count(*) from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where LENGTH(administrative_area_level_1)>2 and country = '" . $organization->country . "' and 
      administrative_area_level_1 = '" . $organization->administrative_area_level_1 . "' order by type_id desc")->queryScalar()>0){
                        
                    //Если есть, тогда дать список всех франшиз с этой областью
                    $pullFranchisees = \Yii::$app->db->createCommand("select * from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where LENGTH(administrative_area_level_1)>2 and country = '" . $organization->country . "' and 
      administrative_area_level_1 = '" . $organization->administrative_area_level_1 . "' order by type_id desc")->queryAll();
                    
                    //проходим по всему пулу франшиз, что подходят, order by type_id дает нам некую автоматизацию, то-есть,
                    //сохранение по приоритам: 1 - спонсор, 2 - предприниматель, 3 startup
                        
                        self::setTypeFranchiseeAndSaveAssoc($pullFranchisees,$organization);
                        $flag = 1;
                    }//А есть ли франшиза с этой страной? 
                    if($flag == 0 && \Yii::$app->db->createCommand("select count(*) from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where country = '" . $organization->country . "' and 
    (locality ='' or locality is null) and 
    (administrative_area_level_1 ='' or administrative_area_level_1 is null) 
    order by type_id desc")->queryScalar()>0){
                    //Если есть, тогда дать список всех франшиз с этой областью
                    $pullFranchisees = \Yii::$app->db->createCommand("select * from franchisee f
                    join `franchisee_geo` fg on (f.`id` = fg.`franchisee_id`)
                    where country = '" . $organization->country . "' and 
    (locality ='' or locality is null) and 
    (administrative_area_level_1 ='' or administrative_area_level_1 is null) 
    order by type_id desc")->queryAll();
                    //проходим по всему пулу франшиз, что подходят, order by type_id дает нам некую автоматизацию, то-есть,
                    //сохранение по приоритам: 1 - спонсор, 2 - предприниматель, 3 startup
                    
                        self::setTypeFranchiseeAndSaveAssoc($pullFranchisees,$organization);
                        
                    }
                }else{
                // нет подходящего франча / в не отсортированные
                $organization_model = Organization::findOne($organization->id);
                $organization_model->franchisee_sorted = 1;
                $organization_model->save();
                
                $franchiseeAssociate = new \common\models\FranchiseeAssociate();
                $franchiseeAssociate->franchisee_id = 1;
                $franchiseeAssociate->organization_id = $organization->id;
                $franchiseeAssociate->self_registered = \common\models\FranchiseeAssociate::SELF_REGISTERED;
                $franchiseeAssociate->save();
                }
            }
        }
    }
    
    static function setTypeFranchiseeAndSaveAssoc($pullFranchisees,$organization){
        
        //проходим по всему пулу франшиз,
        //сохранение по приоритам: 1 - спонсор, 2 - предприниматель, 3 startup
        //var_dump($pullFranchisees);
        foreach($pullFranchisees as $f){
            //var_dump("регион: " . $f['locality'] . " тип: " . $f['type_id']);
            if(($f['locality'] == $organization->locality && $f['exception'] == 1) || 
                    ($f['administrative_area_level_1'] == $organization->administrative_area_level_1 && $f['exception'] == 1)){
                continue;
            }
            
            //Здесь же проверка на уже существующую связь
            if(!\common\models\FranchiseeAssociate::find()->where([
                'organization_id'=>$organization->id])->exists()){
                
                $franchiseeAssociate = new \common\models\FranchiseeAssociate();
                $franchiseeAssociate->franchisee_id = $f['franchisee_id'];
                $franchiseeAssociate->organization_id = $organization->id;
                $franchiseeAssociate->self_registered = \common\models\FranchiseeAssociate::SELF_REGISTERED;
                $franchiseeAssociate->save();
                //Если спонсор, тогда никто больше не претендует на эту организацию 
                //во всех дальнейших итераций
                break;
            }
        }
        $organization = \common\models\Organization::findOne($organization->id);
        $organization->franchisee_sorted = 1;
        $organization->save();
    }
    
    
    public function actionMappingOrganizationFromGoogleApiMaps() {
        $model = Organization::find()->where('lng is not null and lat is not null and country is not null and administrative_area_level_1 is null')->limit(500)->all();
        foreach($model as $s){
            $address_url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.Yii::$app->params['google-api']['key-id'].'&latlng=' . $s->lat . ',' . $s->lng . '&language=ru&sensor=false';
            $address_json = json_decode(file_get_contents($address_url));
            if(!empty($address_json->results[0]->address_components)){
            $address_data = $address_json->results[0]->address_components;
            $location = array();
            $location['locality'] = '';
            $location['admin_1'] = '';
            $location['country'] = '';
            foreach ($address_data as $component) {
              switch ($component->types) {
                case in_array('locality', $component->types):
                  $location['locality'] = $component->long_name;
                  break;
                case in_array('administrative_area_level_1', $component->types):
                  $location['admin_1'] = $component->long_name;
                  break;
                case in_array('country', $component->types):
                  $location['country'] = $component->long_name;
                  break;
              }

            }
        
        $country = $location['country'];
        $locality = $location['locality'];
        $administrative_area_level_1 = $location['admin_1'];
        
        $organization = Organization::findOne($s->id);
        $organization->administrative_area_level_1 = $administrative_area_level_1;
        $organization->save();
            }
        }
    }
    
    public function actionSendMailNewRequests() {
        //
    }
    
    public function actionUpdateBlacklist() {
        Organization::updateAll(["blacklisted" => true], "blacklisted = 0 AND (name LIKE '% test%' OR name LIKE '% тест%')");
    }
}
