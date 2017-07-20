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
use api\modules\v1\modules\mobile\resources\OrderContent;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use common\models\RelationSuppRest;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderContentController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\OrderContent';

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
        $params = new OrderContent();
        $user = Yii::$app->user->getIdentity();
        
        $query = OrderContent::find();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query = OrderContent::find()->where(['in','order_id', Order::find()->select('id')->where(['client_id' => $user->organization_id])]);
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
             $query = OrderContent::find()->where(['in','order_id', Order::find()->select('id')->where(['vendor_id' => $user->organization_id])]);
     
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
        $query->andFilterWhere([
            'id' => $params->id, 
            'product_id' => $params->product_id, 
            'quantity' => $params->quantity, 
            'price' => $params->price, 
            'initial_quantity' => $params->initial_quantity, 
            'units' => $params->units, 
            'article' => $params->article
           ]);
        return $dataProvider;
    }

    
}
