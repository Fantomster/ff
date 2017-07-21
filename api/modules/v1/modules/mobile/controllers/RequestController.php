<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Request;
use yii\data\ActiveDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Request';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

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
        $model = Request::findOne($id);
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
        $params = new Request();
        $query = Request::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        $user = Yii::$app->user->getIdentity();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query->andWhere (['rest_org_id'=>$user->organization_id]);
       
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
         $query->andFilterWhere([
            'id' => $params->id, 
            'category' => $params->category, 
            'product' => $params->product, 
            'comment' => $params->comment, 
            'regular' => $params->regular, 
            'amount' => $params->amount, 
            'rush_order' => $params->rush_order, 
            'payment_method' => $params->payment_method, 
            'deferment_payment' => $params->deferment_payment,
            'responsible_supp_org_id' => $params->responsible_supp_org_id, 
            'count_views' => $params->count_views, 
            'created_at' => $params->created_at, 
            'end' => $params->end, 
            'rest_org_id' => $params->rest_org_id, 
            'active_status' => $params->active_status
           ]);
        return $dataProvider;
    }
}
