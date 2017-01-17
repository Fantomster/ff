<?php

namespace market\controllers;
use yii\web\HttpException;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Order;
use common\models\Organization;
use common\models\Delivery;
use common\models\Role;
use common\models\Profile;
use common\models\search\UserSearch;
use common\models\RelationSuppRest;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\GoodsNotes;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use common\components\AccessRule;
use yii\helpers\Url;
use yii\helpers\Json;

//ini_set('xdebug.max_nesting_level', 200);
/**
 * Site controller
 */
class SiteController extends Controller {
    /**
     * @inheritdoc
     */
    /* public function behaviors()
      {
      return [
      'access' => [
      'class' => AccessControl::className(),
      'rules' => [
      [
      'actions' => ['login', 'error'],
      'allow' => true,
      ],
      [
      'actions' => ['logout', 'index'],
      'allow' => true,
      'roles' => ['@'],
      ],
      ],
      ],
      'verbs' => [
      'class' => VerbFilter::className(),
      'actions' => [
      'logout' => ['post'],
      ],
      ],
      ];
      } */

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (\Yii::$app->user->isGuest) {
        $addwhere = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->asArray()
                    ->all();
            $addwhere = ['not in','supp_org_id',$relationSupplier];
            }
        }
        $topProducts = CatalogBaseGoods::find()->
                where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->limit(6)
                ->all();
        $topSuppliers = CatalogBaseGoods::find()
                ->select('DISTINCT(`supp_org_id`) as supp_org_id')
                ->where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->limit(6)
                ->all();
        $topProductsCount = CatalogBaseGoods::find()
                ->where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->count();
        $topSuppliersCount = CatalogBaseGoods::find()
                ->select('supp_org_id')
                ->where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->distinct()
                ->count();
        return $this->render('/site/index', compact('topProducts','topSuppliers','topProductsCount','topSuppliersCount'));
    }
    
    public function actionProduct($id)
    {
        if (\Yii::$app->user->isGuest) {
        $addwhere = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->asArray()
                    ->all();
            $addwhere = ['not in','supp_org_id',$relationSupplier];
            }
        }
        $product = CatalogBaseGoods::find()
                ->where(['id' => $id, 'market_place' => CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->one();
        
        if ($product) {
            return $this->render('/site/product', compact('product'));
        } else {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
        }

    }
    public function actionSearchProducts($search){
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->all();
                $filterNotIn = [];
                foreach ($suppliers AS $supplier){
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'product_name' => [
                            'query' =>$search,
                            'analyzer' =>"ru",
                            'type' =>'phrase_prefix',
                            'max_expansions' =>6
                        ]
                    ]
                ],
                'filter' => [
                    'bool' => [
                        'must_not' => [
                            'terms' => [
                                'product_supp_id' => $filterNotIn
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Product::find()->query($params)
                            ->limit(10000)->count();
        if (!empty($count)) {
            $products = \common\models\ES\Product::find()->query($params)
                            ->limit(12)->all();
            return $this->render('/site/search-products', compact('count','products','search')); 
        } else {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
        }        
    }
    public function actionAjaxEsProductMore($num,$search)
    {     
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->all();
                $filterNotIn = [];
                foreach ($suppliers AS $supplier){
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'product_name' => [
                            'query' =>$search,
                            'analyzer' =>"ru",
                            'type' =>'phrase_prefix',
                            'max_expansions' =>6
                        ]
                    ]
                ],
                'filter' => [
                    'bool' => [
                        'must_not' => [
                            'terms' => [
                                'product_supp_id' => $filterNotIn//$filterNotIn
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Product::find()->query($params)
                //->where('not in','product_supp_id',$addwhere)
                //->mustNot('product_supp_id','1')
                ->offset($num)
                ->limit(12)
                ->count();
        
        if($count > 0){
        $pr = \common\models\ES\Product::find()->query($params)
                //->where('not in','product_supp_id',$addwhere)
                ->offset($num)
                ->limit(12)
                ->all();   
        return $this->renderPartial('/site/main/_ajaxEsProductMore', compact('pr'));
        }
        
    }
    public function actionSearchSuppliers($search){
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->all();
                $filterNotIn = [];
                foreach ($suppliers AS $supplier){
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'supplier_name' => [
                            'query' =>$search,
                            'analyzer' =>"ru",
                            'type' =>'phrase_prefix',
                            'max_expansions' =>6
                        ]
                    ]
                ],
                'filter' => [
                    'bool' => [
                        'must_not' => [
                            'terms' => [
                                'supplier_id' => $filterNotIn
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Supplier::find()->query($params)
                            ->limit(1000000)->count();
        if (!empty($count)) {
            $sp = \common\models\ES\Supplier::find()->query($params)
                            ->limit(12)->all();
            return $this->render('/site/search-suppliers', compact('count','sp','search')); 
        } else {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
        }        
    }
    public function actionAjaxEsSupplierMore($num,$search)
    {     
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->all();
                $filterNotIn = [];
                foreach ($suppliers AS $supplier){
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        $params = [
            'filtered' => [
                'query' => [
                    'match' => [
                        'supplier_name' => [
                            'query' =>$search,
                            'analyzer' =>"ru",
                            'type' =>'phrase_prefix',
                            'max_expansions' =>6
                        ]
                    ]
                ],
                'filter' => [
                    'bool' => [
                        'must_not' => [
                            'terms' => [
                                'supplier_id' => $filterNotIn
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $count = \common\models\ES\Supplier::find()->query($params)
                ->offset($num)
                ->limit(12)
                ->count();
        
        if($count > 0){
        $sp = \common\models\ES\Supplier::find()->query($params)
                ->offset($num)
                ->limit(12)
                ->all();   
        return $this->renderPartial('/site/main/_ajaxEsSupplierMore', compact('sp'));
        }
        
    }
    public function actionSupplierProducts($id)
    {
        if (\Yii::$app->user->isGuest) {
        $addwhere = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->asArray()
                    ->all();
            $addwhere = ['not in','supp_org_id',$relationSupplier];
            }
        }
        $productsCount = CatalogBaseGoods::find()
                ->where(['supp_org_id' => $id, 'market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->count();
        $products = CatalogBaseGoods::find()
                ->where(['supp_org_id' => $id, 'market_place' => CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->limit(12)->all();
        if ($products) {
            return $this->render('/site/supplier-products', compact('products','id','productsCount'));
            
        } else {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
        }
    }
    public function actionAjaxProductLoader($num,$supp_org_id)
    {     
        
        if (Yii::$app->request->isAjax) {
            $count = CatalogBaseGoods::find()
                    ->where(['supp_org_id' => $supp_org_id, 'market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                    ->offset($num)
                    ->limit(6)
                    ->count();

            if($count > 0){
            $pr = CatalogBaseGoods::find()->where(['supp_org_id' => $supp_org_id, 'market_place'=>CatalogBaseGoods::MARKETPLACE_ON])->offset($num)->limit(6)->all();    
            return $this->renderPartial('/site/main/_ajaxProductMore', compact('pr'));
            }
        }
    }
    
    public function actionSupplier($id)
    {
        $vendor = Organization::findOne(['id' => $id, 'type_id' => Organization::TYPE_SUPPLIER]);
        
        if (\Yii::$app->user->isGuest) {
        $relationSupplier = false;  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT && $vendor) {
            $relationSupplier = RelationSuppRest::find()
                    ->where(['rest_org_id'=>$client->id, 'supp_org_id'=>$vendor->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->exists();
            }
        }
        
        if ($vendor && !$relationSupplier) {
            return $this->render('/site/supplier', compact('vendor'));
        } else {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
        }
    }
    public function actionAjaxProductMore($num)
    {     
        if (\Yii::$app->user->isGuest) {
        $addwhere = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->asArray()
                    ->all();
            $addwhere = ['not in','supp_org_id',$relationSupplier];
            }
        }
        $count = CatalogBaseGoods::find()
                ->where(['market_place'=>1])
                ->andWhere($addwhere)
                ->offset($num)
                ->limit(6)
                ->count();
        if($count > 0){
        $pr = CatalogBaseGoods::find()
                ->where(['market_place'=>1])
                ->andWhere($addwhere)
                ->offset($num)
                ->limit(6)
                ->all();    
        return $this->renderPartial('/site/main/_ajaxProductMore', compact('pr'));
        }
        
    }
    public function actionAjaxSupplierMore($num)
    {      
        if (\Yii::$app->user->isGuest) {
        $addwhere = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id])
                    ->asArray()
                    ->all();
            $addwhere = ['not in','supp_org_id',$relationSupplier];
            }
        }
        $count = CatalogBaseGoods::find()
            ->select('DISTINCT(`supp_org_id`) as supp_org_id')
            ->where(['market_place'=>1])
            ->andWhere($addwhere)
            ->limit(6)->offset($num)        
            ->count();
        if($count > 0){
        $sp = CatalogBaseGoods::find()
            ->select('DISTINCT(`supp_org_id`) as supp_org_id')
            ->where(['market_place'=>1])
            ->andWhere($addwhere)
            ->limit(6)->offset($num)
            ->all();
        return $this->renderPartial('/site/main/_ajaxSupplierMore', compact('sp'));
        }
    }
    public function actionCategory($id)
    {
          if (\Yii::$app->user->isGuest) {
            $addwhere = [];  
            }else{
                $currentUser = Yii::$app->user->identity;
                $client = $currentUser->organization;

                if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                        ->select('supp_org_id')
                        ->where(['rest_org_id'=>$client->id])
                        ->asArray()
                        ->all();
                $addwhere = ['not in','supp_org_id',$relationSupplier];
                }
            }
          $count = CatalogBaseGoods::find()
                  ->where(['market_place'=>1,'category_id'=>$id])
                  ->andWhere($addwhere)
                  ->limit(12)
                  ->count();
          $products = CatalogBaseGoods::find()
                  ->where(['market_place'=>1,'category_id'=>$id])
                  ->andWhere($addwhere)
                  ->limit(12)
                  ->all();         
          if ($products) {
                return $this->render('category', compact('products','id','count'));
            } else {
                throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');    
            }
          throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');                
    }
    
    public function actionSuppliers()
    {
        if (\Yii::$app->user->isGuest) {
            $addwhere = [];  
            }else{
                $currentUser = Yii::$app->user->identity;
                $client = $currentUser->organization;

                if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                        ->select('supp_org_id')
                        ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                        ->asArray()
                        ->all();
                $addwhere = ['not in','supp_org_id',$relationSupplier];
                }
            }
        $suppliers = CatalogBaseGoods::find()
        ->select('DISTINCT(`supp_org_id`) as supp_org_id')
        ->where(['market_place'=>1])
        ->andWhere($addwhere)
        ->limit(12)
        ->all();
        
        $suppliersCount = CatalogBaseGoods::find()
                ->select('supp_org_id')
                ->where(['market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                ->andWhere($addwhere)
                ->distinct()
                ->count();
        
        return $this->render('suppliers', compact('suppliers','suppliersCount'));
    }
    public function actionAjaxProductCatLoader($num,$category)
    {    
        if (\Yii::$app->user->isGuest) {
            $addwhere = [];  
            }else{
                $currentUser = Yii::$app->user->identity;
                $client = $currentUser->organization;

                if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relationSupplier = RelationSuppRest::find()
                        ->select('supp_org_id')
                        ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                        ->asArray()
                        ->all();
                $addwhere = ['not in','supp_org_id',$relationSupplier];
                }
            }
        if (Yii::$app->request->isAjax) {
            $count = CatalogBaseGoods::find()
                    ->where(['category_id' => $category, 'market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                    ->andWhere($addwhere)
                    ->offset($num)
                    ->limit(6)
                    ->count();

            if($count > 0){
            $pr = CatalogBaseGoods::find()
                    ->where(['category_id' => $category, 'market_place'=>CatalogBaseGoods::MARKETPLACE_ON])
                    ->andWhere($addwhere)
                    ->offset($num)
                    ->limit(6)
                    ->all();    
            return $this->renderPartial('/site/main/_ajaxProductMore', compact('pr'));
            }
        }
    }
    //в перспективе запрос поменяю, будет мапиться только то, что добавлено в МП
    public function actionCurlAddProduct() {
        
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '128M');

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
    $model = \common\models\CatalogBaseGoods::find()
    ->where(['market_place' => \common\models\CatalogBaseGoods::MARKETPLACE_ON])->limit(1000)
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
                "product_created_at"  => $product_created_at
            ];
            $product->save();
        }
    $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
    $res = shell_exec($url);
    echo memory_get_usage().'\n';
    var_dump($model);
    return $res;
    }
    
    
    
    
    
    
    
    
    public function actionCurlAddSupplier() {
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '256M');

        $url = 'curl -XPUT \'http://localhost:9200/supplier\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "ru": {
                                            "type": "custom",
                                            "tokenizer": "standard",
                                            "filter": ["lowercase", "russian_morphology", "my_stopwords"]
                                    }
                            },
                            "filter": {
                                    "my_stopwords": {
                                            "type": "stop",
                                            "stopwords": "а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,ко,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,очень,по,под,при,с,со,так,также,такой,там,те,тем,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                                    }
                            }
                    }
            }
        }\' && echo
        curl -XPUT \'http://localhost:9200/supplier/supplier/_mapping\' -d \'{
                "supplier": {
                    "_all" : {"analyzer" : "russian_morphology"},
                "properties" : {
                        "supplier_name" : { "type" : "string", "analyzer" : "russian_morphology" }
                }
                }
        }\'
        ';
        $res = shell_exec($url);
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
        return $res;
    }
    public function actionCurlAddCategory() {
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '256M');

        $url = 'curl -XPUT \'http://localhost:9200/category\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "ru": {
                                            "type": "custom",
                                            "tokenizer": "standard",
                                            "filter": ["lowercase", "russian_morphology", "my_stopwords"]
                                    }
                            },
                            "filter": {
                                    "my_stopwords": {
                                            "type": "stop",
                                            "stopwords": "а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,ко,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,очень,по,под,при,с,со,так,также,такой,там,те,тем,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                                    }
                            }
                    }
            }
        }\' && echo
        curl -XPUT \'http://localhost:9200/category/category/_mapping\' -d \'{
                "category": {
                    "_all" : {"analyzer" : "russian_morphology"},
                "properties" : {
                        "category_name" : { "type" : "string", "analyzer" : "russian_morphology" }
                }
                }
        }\'
        ';
        $res = shell_exec($url);
        $sql = "select id,name from mp_category where parent is not null";
        $model = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($model as $name) {
            $category_id = $name['id'];
            $category_image = '';
            $category_name = $name['name'];
            $category = new \common\models\ES\Category();
            $category->attributes = [
                "category_id" => $category_id,
                "category_image" => $category_image,
                "category_name" => $category_name
            ];
            $category->save();
        }
        $url = 'curl -XPOST \'http://localhost:9200/category/_refresh\'';
        $res = shell_exec($url);
        var_dump($model);
        return $res;
    }
    public function actionView() {
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;

            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $suppliers = RelationSuppRest::find()
                    ->select('supp_org_id')
                    ->where(['rest_org_id'=>$client->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->all();
                $filterNotIn = [];
                foreach ($suppliers AS $supplier){
                    $filterNotIn[] = $supplier->supp_org_id;
                }
            }
        }
        $search = "";
        $search_categorys_count = "";
        $search_products_count = "";
        $search_suppliers_count = "";
        $search_categorys = "";
        $search_products = "";
        $search_suppliers = "";
        if (isset($_POST['searchText'])) {
            $search = $_POST['searchText'];
            $params_categorys = [
                'query' => [
                    'match' => [
                        'category_name' => $search
                    ]
                ]
            ];
            $params_products = [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'product_name' => [
                                'query' =>$search,
                                'analyzer' =>"ru",
                                'type' =>'phrase_prefix',
                                'max_expansions' =>6
                            ]
                        ]
                    ],
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                'terms' => [
                                    'product_supp_id' => $filterNotIn
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $params_suppliers = [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'supplier_name' => [
                                'query' =>$search
                            ]
                        ]
                    ],
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                'terms' => [
                                    'supplier_id' => $filterNotIn
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $search_categorys_count = \common\models\ES\Category::find()->query($params_categorys)
                            ->limit(10000)->count();
            $search_products_count = \common\models\ES\Product::find()->query($params_products)
                            ->limit(10000000)->count();
            $search_suppliers_count = \common\models\ES\Supplier::find()->query($params_suppliers)
                            ->limit(10000000)->count();
            
            $search_categorys = \common\models\ES\Category::find()->query($params_categorys)
                            ->limit(1000)->asArray()->all();
            $search_products = \common\models\ES\Product::find()->query($params_products)
                            /* ->highlight([
                              "pre_tags"  => "<em>",
                              "post_tags" => "</em>",
                              'fields'    => [
                              'product_name' => new \stdClass()
                              ]
                              ]) */
                            ->limit(4)->asArray()->all();
            $search_suppliers = \common\models\ES\Supplier::find()->query($params_suppliers)
                            ->limit(4)->asArray()->all();
        }

        return $this->renderAjax('main/_search_form', compact(
                                'search_categorys_count','search_products_count', 'search_suppliers_count', 
                                'search_categorys', 'search_products', 'search_suppliers', 'search'));
    }

    public function actionAjaxAddToCart() {

        $currentUser = Yii::$app->user->identity;
        $client = $currentUser->organization;

        if ($client->type_id !== Organization::TYPE_RESTAURANT) {
            return false;
        }

        $orders = $client->getCart();

        $post = Yii::$app->request->post();
        $relation = null;

        if ($post && $post['product_id']) {
            $product = CatalogBaseGoods::findOne(['id' => $post['product_id']]);
            if (empty($product)) {
                return false;
            }
            $relation = RelationSuppRest::findOne(['supp_org_id' => $product->vendor->id, 'rest_org_id' => $client->id]);
            if ($relation && ($relation->invite === RelationSuppRest::INVITE_ON)) {
                return false;
            }
        } else {
            return false;
        }

        $isNewOrder = true;

        foreach ($orders as $order) {
            if ($order->vendor_id == $product->vendor->id) {
                $isNewOrder = false;
                $alteringOrder = $order;
            }
        }
        if ($isNewOrder) {
            $newOrder = new Order();
            $newOrder->client_id = $client->id;
            $newOrder->vendor_id = $product->vendor->id;
            $newOrder->status = Order::STATUS_FORMING;
            $newOrder->save();
            $alteringOrder = $newOrder;
        }

        $isNewPosition = true;
        foreach ($alteringOrder->orderContent as $position) {
            if ($position->product_id == $product->id) {
                $isNewPosition = false;
            }
        }
        if ($isNewPosition) {
            $position = new OrderContent();
            $position->order_id = $alteringOrder->id;
            $position->product_id = $product->id;
            $position->quantity = 1;
            $position->price = $product->price;
            $position->product_name = $product->product;
            $position->units = $product->units;
            $position->article = $product->article;
            $position->save();
        }

        $alteringOrder->calculateTotalPrice();
        $cartCount = $client->getCartCount();
        if (!$relation) {
            $client->inviteVendor($product->vendor, RelationSuppRest::INVITE_OFF, RelationSuppRest::CATALOG_STATUS_OFF);
        }
        $this->sendCartChange($client, $cartCount);

        return true;
    }
    
    public function actionAjaxInviteVendor() {
        
        $currentUser = Yii::$app->user->identity;
        $client = $currentUser->organization;

        if ($client->type_id !== Organization::TYPE_RESTAURANT) {
            return false;
        }

        $post = Yii::$app->request->post();

        if ($post && $post['vendor_id']) {
            $vendor = Organization::findOne(['id' => $post['vendor_id'], 'type_id' => Organization::TYPE_SUPPLIER]);
            if (empty($vendor)) {
                return false;
            }
            $relation = RelationSuppRest::findOne(['supp_org_id' => $vendor->id, 'rest_org_id' => $client->id]);
            if ($relation) {
                return false;
            }
        } else {
            return false;
        }

        $client->inviteVendor($vendor, RelationSuppRest::INVITE_OFF, RelationSuppRest::CATALOG_STATUS_OFF);

        return true;
    }

    private function sendCartChange($client, $cartCount) {
        $clientUsers = $client->users;

        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $cartCount, 'channel' => $channel, 'isSystem' => 2])
            ]);
        }

        return true;
    }
    
    
    
    
    
    public function deleteCollection() {
        
        $es_product = \common\models\ES\Product::find()->where(['product_id'=>$product_id])->one();
        $es_product->delete();
    }
}
