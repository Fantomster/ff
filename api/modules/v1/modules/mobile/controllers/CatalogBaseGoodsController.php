<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogBaseGoodsController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\CatalogBaseGoods';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'only' => ['index', 'view', 'options'],
            'authMethods' => [
                [
                    'class' => HttpBasicAuth::className(),
                    'auth' => function ($username, $password) {
            
                        $model = new LoginForm();
                        $model->email = $username;
                        $model->password = $password;
                        $model->validate();
                        return ($model->validate()) ? $model->getUser() : null;
                    }
                ],
                HttpBearerAuth::className(),
                QueryParamAuth::className()
            ]
        ];
                
        $behaviors['contentNegotiator'] = [
        'class' => ContentNegotiator::className(),
        'formats' => [
            'application/json' => Response::FORMAT_JSON
        ]

        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = Catalog::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
    
    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = new CatalogBaseGoods();
        $user = Yii::$app->user->getIdentity();

        $query = CatalogBaseGoods::find();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query = CatalogBaseGoods::find()->where(['in','id', 
            CatalogGoods::find()->select('base_goods_id')->where(['in','cat_id', 
                RelationSuppRest::find()->select('cat_id')->where(['rest_org_id' => $user->organization_id])])
            ]);
        
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
            $query = CatalogBaseGoods::find()->where(['in','id', 
            CatalogGoods::find()->select('base_goods_id')->where(['in','cat_id', 
                RelationSuppRest::find()->select('cat_id')->where(['supp_org_id' => $user->organization_id])])
            ]);
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
        $query->andFilterWhere([
            'id' => $params->id, 
            'cat_id' => $params->cat_id, 
            'article' => $params->article, 
            'product' => $params->product, 
            'status' => ($params->status == null)?CatalogBaseGoods::STATUS_ON:$params->status, 
            'market_place' => $params->market_place, 
            'deleted' => $params->deleted, 
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at, 
            'category_id' => $params->category_id, 
            'note' => $params->note, 
            'ed' => $params->ed, 
            'brand' => $params->brand, 
            'region' => $params->region, 
            'weight' => $params->weight, 
            'es_status' => $params->es_status, 
            'mp_show_price' => $params->mp_show_price, 
            'rating' => $params->rating
           ]);
        return $dataProvider;
    }

    
}
