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
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Order';

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
        $params = new Order();
        $query = Order::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();
        
        $filters['client_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->organization_id : $params->client_id;
        $filters['vendor_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->vendor_id;
          
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }
  
       
            $filters['id'] = $params->id; 
            $filters['created_by_id'] = $params->cat_id; 
            $filters['accepted_by_id'] = $params->invite; 
            $filters['status'] = $params->status; 
            $filters['total_price'] = $params->total_price; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
            $filters['requested_delivery'] = $params->requested_delivery; 
            $filters['actual_delivery'] = $params->actual_delivery;
            $filters['comment'] = $params->comment;
            $filters['discount'] = $params->discount;
            $filters['discount_type'] = $params->discount_type;
  
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }
}
