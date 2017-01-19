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
            $addwhere = [];     
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
            $addwhere = []; 

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
            $filterNotIn = []; 
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
                            //'type' =>'phrase_prefix',
                            //'max_expansions' =>6
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
            $filterNotIn = [];
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
                            //'type' =>'phrase_prefix',
                            //'max_expansions' =>6
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
            $filterNotIn = []; 
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
                            //'analyzer' =>"ru",
                            //'type' =>'phrase_prefix',
                           // 'max_expansions' =>6
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
                            ->limit(10000)->count();
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
            $filterNotIn = []; 
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
                            //'analyzer' =>"ru",
                            //'type' =>'phrase_prefix',
                            //'max_expansions' =>6
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
            $addwhere = []; 
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
        $vendor = \common\models\Organization::find()->where(['id'=>$id])->one();;
        if ($products) {
            return $this->render('/site/supplier-products', compact('products','id','vendor','productsCount'));
            
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
            $addwhere = []; 
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
            $relationSupplier = RelationSuppRest::find()
                    ->where(['rest_org_id'=>$client->id, 'supp_org_id'=>$vendor->id,'status' => RelationSuppRest::CATALOG_STATUS_ON])
                    ->exists();
            }
            if ($client->type_id == Organization::TYPE_SUPPLIER) {
            $addwhere = [];    
            $relationSupplier = false;
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
            $addwhere = []; 
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
            $addwhere = []; 
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
                $addwhere = []; 
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
          $category = \common\models\MpCategory::find()->where(['id'=>$id])->one();
          if ($products) {
                return $this->render('category', compact('products','id','count','category'));
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
                $addwhere = []; 
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
                $addwhere = []; 
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
    
    public function actionView() {
        if (\Yii::$app->user->isGuest) {
        $filterNotIn = [];  
        }else{
            $currentUser = Yii::$app->user->identity;
            $client = $currentUser->organization;
            $filterNotIn = []; 
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
                                //'type' =>'phrase_prefix',
                                //'max_expansions' =>6
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
}
