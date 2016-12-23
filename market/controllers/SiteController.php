<?php

namespace market\controllers;

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
use common\components\AccessRule;
use yii\helpers\Url;
//ini_set('xdebug.max_nesting_level', 200);
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    /*public function behaviors()
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
    }*/

    /**
     * @inheritdoc
     */
    public function actions()
    {
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
        return $this->render('/site/index');
    }
    public function actionFilter()
    {
        return $this->render('filter');
    }
    //в перспективе запрос поменяю, будет мапиться только то, что добавлено в МП
     public function actionCurlAddProduct()
    {
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '256M');
        
    $url = 'curl -XPUT \'http://localhost:9200/product\' -d \'{
    "settings": {
		"analysis": {
			"analyzer": {
				"my_analyzer": {
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
    curl -XPUT \'http://localhost:9200/product/product/_mapping\' -d \'{
            "product": {
                "_all" : {"analyzer" : "russian_morphology"},
            "properties" : {
                    "product_name" : { "type" : "string", "analyzer" : "russian_morphology" }
            }
            }
    }\'
    '; 
    $res = shell_exec($url);
        $model = \common\models\CatalogBaseGoods::find()->select(['id','image','product','price','created_at'])->all();
        foreach ($model as $name) {
            $product_id = $name->id;
            $product_image = !empty($name->image) ? $name->imageUrl : ''; 
            $product_name = $name->product; 
            $product_price = $name->price; 
            $product_created_at = $name->created_at;
            $product = new \common\models\ES\Product();
            $product->attributes = [  
                "product_id" => $product_id,
                "product_image" => $product_image,
                "product_name"  => $product_name,
                "product_price"  => $product_price,
                "product_created_at" => $product_created_at
            ];
            $product->save();
        }
    $url = 'curl -XPOST \'http://localhost:9200/product/_refresh\'';
    $res = shell_exec($url);
    return $res;
    }
    
     public function actionCurlAddSupplier()
    {
        ini_set("max_execution_time", "180");
        ini_set('memory_limit', '256M');
        
        $url = 'curl -XPUT \'http://localhost:9200/supplier\' -d \'{
        "settings": {
                    "analysis": {
                            "analyzer": {
                                    "my_analyzer": {
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
                "supplier_name"  => $supplier_name
            ];
            $suppliers->save();
        }
        $url = 'curl -XPOST \'http://localhost:9200/supplier/_refresh\'';
        $res = shell_exec($url);
        return $res;
    }
    public function actionView()
    {
    $search="";
    $search_products_count="";
    $search_suppliers_count="";
    $search_products="";
    $search_suppliers="";
    if (isset($_POST['searchText'])) {
        $search = $_POST['searchText'];
        $params_products = [
                'query'  => [
                    'match' => [
                        'product_name' => $search
                    ]
                 ]
            ];
        $params_suppliers = [
                'query'  => [
                    'match' => [
                        'supplier_name' => $search
                    ]
                 ]
            ];
        $search_products_count = \common\models\ES\Product::find()->query($params_products)
                ->limit(10000000)->count();
        $search_suppliers_count = \common\models\ES\Product::find()->query($params_suppliers)
                ->limit(10000000)->count();
        
        $search_products = \common\models\ES\Product::find()->query($params_products)
               /* ->highlight([
                    "pre_tags"  => "<em>",
                    "post_tags" => "</em>",
                    'fields'    => [
                        'product_name' => new \stdClass()
                    ]
                ])*/
                ->limit(4)->asArray()->all();  
        $search_suppliers = \common\models\ES\Supplier::find()->query($params_suppliers)
                ->limit(6)->asArray()->all(); 
        }
        
        return $this->renderAjax('main/_search_form', compact(
                'search_products_count',
                'search_suppliers_count',
                'search_products',
                'search_suppliers',
                'search'));
    }
    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
