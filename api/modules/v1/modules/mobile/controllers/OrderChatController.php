<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\OrderChat;
use yii\data\ActiveDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderChatController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\OrderChat';

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
        $model = OrderChat::findOne($id);
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
        $params = new OrderChat();
        $query = OrderChat::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();
        
        $filters['sent_by_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->id : $params->sent_by_id;
        $filters['recipient_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->recipient_id;
          
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }
  
       
            $filters['id'] = $params->id; 
            $filters['order_id'] = $params->order_id; 
            $filters['is_system'] = $params->is_system;
            $filters['message'] = $params->message;
            $filters['created_at'] = $params->created_at;
            $filters['viewed'] = $params->viewed;
            $filters['recipient_id'] = $params->recipient_id;
            $filters['danger'] = $params->danger;

            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }
}
