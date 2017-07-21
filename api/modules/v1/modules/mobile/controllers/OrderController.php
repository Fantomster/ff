<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
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
        $model = Order::findOne($id);
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
