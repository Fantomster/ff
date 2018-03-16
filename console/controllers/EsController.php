<?php

namespace console\controllers;
use Yii;
use yii\console\Controller;

class EsController extends Controller
{
    //Создание 3х коллекций 
    //Category / Product / Supplier
    public function actionCreateIndexes() {
    ini_set("max_execution_time", "180");
    ini_set('memory_limit', '128M');
    
    $settings = '
        "settings": {
		"analysis": {
			"analyzer": {
                                "default": {
                                        "type": "custom",
                                        "tokenizer": "mc_tokenizer",
                                        "char_filter": [
                                            "mc_char_filter"
                                        ],
                                        "filter": [
                                            "ru_stopwords",
                                            "lowercase",
                                            "russian_morphology",
                                            "english_morphology",
                                            "spanish_morphology",
                                            "snowball"
                                        ]
                                },
				"ru": {
                                        "type": "custom",
                                        "tokenizer": "standard",
                                        "char_filter": [
                                            "mc_char_filter"
                                        ],
                                        "filter": [
                                            "ru_stopwords",
                                            "lowercase",
                                            "russian_morphology",
                                            "english_morphology",
                                            "spanish_morphology",
                                            "snowball"
                                        ]
				}
			},
                        "char_filter": {
                            "mc_char_filter": {
                                "type": "mapping",
                                "mappings": [
                                    "ё => е",
                                    "Ё => е",
                                    "` => ё",
				    "q => й",
				    "w => ц",
				    "e => у",
				    "r => к",
				    "t => е",
				    "y => н",
				    "u => г",
				    "i => ш",
				    "o => щ",
				    "p => з",
				    "[ => х",
				    "] => ъ",
				    "a => ф",
				    "s => ы",
				    "d => в",
				    "f => а",
				    "g => п",
				    "h => р",
				    "j => о",
				    "k => л",
				    "l => д",
				    "; => ж",
				    "\" => э",
				    "z => я",
				    "x => ч",
				    "c => с",
				    "v => м",
				    "b => и",
				    "n => т",
				    "m => ь",
				    ", => б",
				    ". => ю"                                ]
                            }
                        },
                        "tokenizer": {
                            "mc_tokenizer": {
                                "type": "ngram",
                                "min_gram": 3,
                                "max_gram": 20,
                                "token_chars": [
                                    "letter",
                                    "digit"
                                ]
                            }
                        },
			"filter": {
				"ru_stopwords": {
					"type": "stop",
					"stopwords": "а,более,бы,был,была,были,было,быть,в,вам,во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко, кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от, по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя, чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
				},
                                "snowball": {
                                    "type": "snowball",
                                    "language": "russian"
                                }
			}
		}
	}';
    
    $host = Yii::$app->elasticsearch->nodes[0]['http_address'];
    $url = 'curl -XPUT \'http://' . $host . '/category\' -d \'{
    '.$settings.'
    }\' && echo
    curl -XPUT \'http://' . $host . '/category/category/_mapping\' -d \'{
            "category": {
                "properties" : {
                        "category_id" : {"type" : "long"},
                        "category_slug" : { 
                            "type" : "string"
                        },
                        "category_name" : { 
                            "type" : "string"
                        },
                        "category_sub_id" : {"type" : "long"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url);  
    
    $url = 'curl -XPUT \'http://' . $host . '/product\' -d \'{
    '.$settings.'
    }\' && echo
    curl -XPUT \'http://' . $host . '/product/product/_mapping\' -d \'{
            "product": {
                "properties" : {
                        "product_id"  :{"type" : "long"},
                        "product_name" : { 
                            "type" : "string", 
                            "term_vector" : "yes"
                        },
                        "product_supp_id" : {"type" : "long"},
                        "product_supp_name" : {"type" : "string"},
                        "product_image" : {"type" : "string"},
                        "product_price" : {"type" : "string"},
                        "product_currency" : {"type" : "string"},
                        "product_category_id" : {"type" : "long"},
                        "product_category_sub_id" : {"type" : "long"},
                        "product_category_name" : {"type" : "string"},
                        "product_category_sub_name" : {"type" : "string"},
                        "product_created_at" : {"type" : "string"},
                        "product_show_price" : {"type" : "long"},
                        "product_rating" : {"type" : "long"},
                        "product_partnership" : {"type" : "long"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url); 
    
    
    $url = 'curl -XPUT \'http://' . $host . '/supplier\' -d \'{
    '.$settings.'
    }\' && echo
    curl -XPUT \'http://' . $host . '/supplier/supplier/_mapping\' -d \'{
            "supplier": {
                "properties" : {
                        "supplier_id" : {"type" : "long"},
                        "supplier_name" : { 
                            "type" : "string", 
                            "term_vector" : "yes"
                        },
                        "supplier_image" : {"type" : "string"},
                        "supplier_rating" : {"type" : "long"},
                        "supplier_partnership" : {"type" : "long"}
                }
            }
    }\'
    '; 
    $res = shell_exec($url);
    }
    
    public function actionCreateAndMappingSuppliers(){
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '128M');

        $host = Yii::$app->elasticsearch->nodes[0]['http_address'];
        $url = 'curl -XPUT \'http://' . $host . '/supplier\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "ru": {
                                            "type": "custom",
                                            "tokenizer": "lowercase",
                                            "filter": ["lowercase", "russian_morphology", "ru_stopwords"]
                                    }
                            },
                            "filter": {
                                    "ru_stopwords": {
                                            "type": "stop",
                                            "stopwords": "а,более,бы,был,была,были,было,быть,в,вам,во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко,кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от,по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя,чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                                    }
                            }
                    }
            }
        }\' && echo
        curl -XPUT \'http://' . $host . '/supplier/supplier/_mapping\' -d \'{
                "supplier": {
                    "properties" : {
                            "supplier_id" : {"type" : "long"},
                            "supplier_name" : { 
                                "type" : "string", 
                                "analyzer" : "ru",
                            },
                            "supplier_image" : {"type" : "string"},
                            "supplier_rating" : {"type" : "long"},
                            "supplier_partnership" : {"type" : "long"}
                    }
                }
        }\'
        '; 
        $res = shell_exec($url);    
    }
    public function actionCreateAndMappingCategory(){
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '128M');

        $host = Yii::$app->elasticsearch->nodes[0]['http_address'];
        $url = 'curl -XPUT \'http://' . $host . '/category\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "ru": {
                                            "type": "custom",
                                            "tokenizer": "lowercase",
                                            "filter": ["lowercase", "russian_morphology", "ru_stopwords"]
                                    }
                            },
                            "filter": {
                                    "ru_stopwords": {
                                            "type": "stop",
                                            "stopwords": "а,более,бы,был,была,были,было,быть,в,вам,во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко,кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от,по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя,чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                                    }
                            }
                    }
            }
        }\' && echo
        curl -XPUT \'http://' . $host . '/category/category/_mapping\' -d \'{
                "category": {
                    "properties" : {
                            "category_id" : {"type" : "long"},
                            "category_slug" : {"type" : "string"},
                            "category_name" : {"type" : "string"},
                            "category_sub_id" : {"type" : "long"}
                    }
                }
        }\'
        '; 
        $res = \shell_exec($url);   
    }
    public function actionCreateAndMappingProduct(){
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '128M');

        $host = Yii::$app->elasticsearch->nodes[0]['http_address'];
        $url = 'curl -XPUT \'http://' . $host . '/product\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "ru": {
                                            "type": "custom",
                                            "tokenizer": "lowercase",
                                            "filter": ["lowercase", "russian_morphology", "ru_stopwords"]
                                    }
                            },
                            "filter": {
                                    "ru_stopwords": {
                                            "type": "stop","stopwords": "а,более,бы,был,была,были,было,быть,в,вам,во,вот,всего,да,даже,до,если,еще,же,за,и,из,или,им,их,к,как,ко,кто,ли,либо,мне,может,на,надо,не,ни,них,но,ну,о,об,от,по,под,при,с,со,так,также,те,тем,то,того,тоже,той,том,у,уже,хотя,чье,чья,эта,эти,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                                    }
                            }
                    }
            }
        }\' && echo
        curl -XPUT \'http://' . $host . '/product/product/_mapping\' -d \'{
                "product": {
                    "properties" : {
                            "product_id"  :{"type" : "long"},
                            "product_name" : { 
                                "type" : "string", 
                            },
                            "product_supp_id" : {"type" : "long"},
                            "product_supp_name" : {"type" : "string"},
                            "product_image" : {"type" : "string"},
                            "product_price" : {"type" : "string"},
                            "product_currency" : {"type" : "string"},
                            "product_category_id" : {"type" : "long"},
                            "product_category_sub_id" : {"type" : "long"},
                            "product_category_name" : {"type" : "string"},
                            "product_category_sub_name" : {"type" : "string"},
                            "product_created_at" : {"type" : "string"},
                            "product_show_price" : {"type" : "long"},
                            "product_rating" : {"type" : "long"},
                            "product_partnership" : {"type" : "long"}
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
            $category_slug = $name->slug;
            //ru
            $category = new \common\models\ES\Category();
            $category->attributes = [
                "category_id" => $category_id,
                "category_sub_id" => $category_sub_id,
                "category_name" => Yii::t('app', $category_name, 'ru'),
                "category_slug" => $category_slug,
            ];
            $category->save();
            //en
            $category = new \common\models\ES\Category();
            $category->attributes = [
                "category_id" => $category_id,
                "category_sub_id" => $category_sub_id,
                "category_name" => Yii::t('app', $category_name, 'en'),
                "category_slug" => $category_slug,
            ];
            $category->save();
            //es
            $category = new \common\models\ES\Category();
            $category->attributes = [
                "category_id" => $category_id,
                "category_sub_id" => $category_sub_id,
                "category_name" => Yii::t('app', $category_name, 'es'),
                "category_slug" => $category_slug,
            ];
            $category->save();
        }
    }
    public function actionUpdateSupplier() {
        $suppliers = \common\models\Organization::find()
                ->where([
                    'type_id' => \common\models\Organization::TYPE_SUPPLIER,
                    'white_list' => \common\models\Organization::WHITE_LIST_ON])
                ->andWhere('locality is not null and locality <> \'undefined\'')
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
    }
    public function actionDeleleProductCollection(){
    
    }
    public function actionTest(){
    $url = 'curl -XPOST \'http://' . $host . '/product/_open\' ';
    $res = shell_exec($url);
    }
}