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
                        "category_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        }
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
                        "product_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        }
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
                        "supplier_name" : { 
                            "type" : "string", 
                            "analyzer" : "ru",
                            "term_vector" : "with_positions_offsets"
                        }
                }
            }
    }\'
    '; 
    $res = shell_exec($url);
    }
}