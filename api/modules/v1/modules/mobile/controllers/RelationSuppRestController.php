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
use api\modules\v1\modules\mobile\resources\RelationSuppRest;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\models\User;
use common\models\forms\LoginForm;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RelationSuppRestController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\RelationSuppRest';

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
        $model = RelationSuppRest::findOne($id);
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
        $params = new RelationSuppRest();
        $query = RelationSuppRest::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();
        
        $filters['rest_org_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->organization_id : $params->rest_org_id;
        $filters['supp_org_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->supp_org_id;
          
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }
  
       
            $filters['id'] = $params->id; 
            $filters['cat_id'] = $params->cat_id; 
            $filters['invite'] = $params->invite; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
            $filters['status'] = $params->status; 
            $filters['uploaded_catalog'] = $params->uploaded_catalog; 
            $filters['uploadded_processed'] = $params->uploaded_catalog;
            $filters['is_from_market'] = $params->is_from_market;
            
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }

    
}
