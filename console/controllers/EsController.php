<?php

namespace console\controllers;

use yii\console\Controller;

class EsController extends Controller
{
    //Создание 3х коллекций 
    //Category / Product / Supplier
    public function actionCreateIndexes() {
    ini_set("max_execution_time", "180");
    ini_set('memory_limit', '128M');

    $url = 'curl -XPUT \'http://localhost:9200/category\' -d \'{
    "settings": {
                "number_of_shards": 1,
                "number_of_replicas": 0,
		"analysis": {
			"analyzer": {
				"ru": {
					"type": "custom",
					"tokenizer": "whitespace",
					"filter": ["lowercase", "russian_morphology", "ru_stopwords"]
				}
			},
			"filter": {
				"ru_stopwords": {
					"type": "stop",
					"stopwords": "а,более,бы,был,была,были,было,быть,в,вам, во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко, кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от, по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя, чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
				}
			}
		}
	}
    }\' && echo
    curl -XPUT \'http://localhost:9200/category/category/_mapping\' -d \'{
            "category": {
                "properties" : {
                        "category_id" : {"type" : "long"},
                        "category_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        },
                        "category_sub_id" : {"type" : "long"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url);  
    
    $url = 'curl -XPUT \'http://localhost:9200/product\' -d \'{
    "settings": {
                "number_of_shards": 1,
                "number_of_replicas": 0,
		"analysis": {
			"analyzer": {
				"ru": {
					"type": "custom",
					"tokenizer": "whitespace",
					"filter": ["lowercase", "russian_morphology", "ru_stopwords"]
				}
			},
			"filter": {
				"ru_stopwords": {
					"type": "stop",
					"stopwords": "а,более,бы,был,была,были,было,быть,в,вам, во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко, кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от, по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя, чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
				}
			}
		}
	}
    }\' && echo
    curl -XPUT \'http://localhost:9200/product/product/_mapping\' -d \'{
            "product": {
                "properties" : {
                        "product_id"  :{"type" : "long"},
                        "product_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        },
                        "product_supp_id" : {"type" : "long"},
                        "product_supp_name" : {"type" : "string"},
                        "product_image" : {"type" : "string"},
                        "product_price" : {"type" : "string"},
                        "product_category_id" : {"type" : "long"},
                        "product_category_sub_id" : {"type" : "long"},
                        "product_category_name" : {"type" : "string"},
                        "product_category_sub_name" : {"type" : "string"},
                        "product_created_at" : {"type" : "string"},
                        "product_show_price" : {"type" : "long"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url); 
    
    
    $url = 'curl -XPUT \'http://localhost:9200/supplier\' -d \'{
    "settings": {
                "number_of_shards": 1,
                "number_of_replicas": 0,
		"analysis": {
			"analyzer": {
				"ru": {
					"type": "custom",
					"tokenizer": "whitespace",
					"filter": ["lowercase", "russian_morphology", "ru_stopwords"]
				}
			},
			"filter": {
				"ru_stopwords": {
					"type": "stop",
					"stopwords": "а,более,бы,был,была,были,было,быть,в,вам, во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко, кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от, по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя, чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
				}
			}
		}
	}
    }\' && echo
    curl -XPUT \'http://localhost:9200/supplier/supplier/_mapping\' -d \'{
            "supplier": {
                "properties" : {
                        "supplier_id" : {"type" : "long"},
                        "supplier_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        },
                        "supplier_image" : {"type" : "string"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url);
    }
    
    public function actionUpdateCategory() {
    ini_set("max_execution_time", "180");
    ini_set('memory_limit', '128M');
    
    $model = \common\models\MpCategory::find()->where('parent is not null')->all();
    foreach ($model as $name) {
        $category_id = $name->parent;
        $category_sub_id = $name->id;
        $category_name = $name->name;
        $category = new \common\models\ES\Category();
        $category->attributes = [
            "category_id" => $category_id,
            "category_sub_id" => $category_sub_id,
            "category_name" => $category_name
        ];
        $category->save();
    }
    //var_dump($model);
    $url = 'curl -XPOST \'http://localhost:9200/category/_refresh\'';
    $res = shell_exec($url);
    }
    public function actionUpdateProduct() {
    ini_set("max_execution_time", "180");
    ini_set('memory_limit', '128M');
    
    $model = \common\models\CatalogBaseGoods::find()
    ->where(['market_place' => \common\models\CatalogBaseGoods::MARKETPLACE_ON, 'es_status' => 3])->limit(1300)
    ->all();
    foreach ($model as $name) {
        $product_id = $name->id;
        $product_image = !empty($name->image) ? $name->imageUrl : ''; 
        $product_name = $name->product; 
        $product_supp_id = $name->supp_org_id;
        $product_supp_name = $name->vendor->name; 
        $product_price = $name->price; 
        $product_category_id = $name->category->parent; 
        $product_category_name = \common\models\MpCategory::find()->where(['id'=>$name->category->parent])->one()->name; 
        $product_category_sub_id = $name->category->id; 
        $product_category_sub_name = $name->category->name;
        $product_created_at = $name->created_at;
        $product_show_price = $name->mp_show_price;
        $product = new \common\models\ES\Product();
        $product->attributes = [
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
            "product_created_at"  => $product_created_at,
            "product_show_price" => $product_show_price,
        ];
        $product->save();
        \common\models\CatalogBaseGoods::updateAll(['es_status' => 0], ['id' => $name->id]);
    }
    $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
    $res = shell_exec($url);
    }
    public function actionUpdateSupplier() {
    ini_set("max_execution_time", "180");
    ini_set('memory_limit', '128M');
    
    $sql = "SELECT organization.id as id, organization.name as name 
            FROM user JOIN organization ON user.organization_id = organization.id
            WHERE type_id = 2";
        $model = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($model as $name) {
            $supplier_id = $name['id'];
            $supplier_image = '';
            $supplier_name = $name['name'];
            $suppliers = new \common\models\ES\Supplier();
            $suppliers->attributes = [
                "supplier_id" => $supplier_id,
                "supplier_image" => $supplier_image,
                "supplier_name" => $supplier_name
            ];
            $suppliers->save();
        }
        
        $url = 'curl -XPOST \'http://localhost:9200/supplier/_refresh\'';
        $res = shell_exec($url);
    }
    public function actionDeleleProductCollection(){
    // 
    $es = \common\models\ES\Product::find()->query($params);
    //
    }
    
}