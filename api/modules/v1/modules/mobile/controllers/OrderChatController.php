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
            'pagination' => false,
        ));
        $filters = [];
       // $user = Yii::$app->user->getIdentity();
        
        /*$filters['sent_by_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->id : $params->sent_by_id;
        $filters['recipient_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->recipient_id;*/
         
        $query->select(
                'order_chat.*,profile.full_name, '
              . 'organization.name as organization_name, organization.picture as organization_picture')
            ->from('order_chat')
            ->innerJoin('user', 'user.id = order_chat.sent_by_id')
            ->innerJoin('user as sender', 'sender.id = '.Yii::$app->user->id)
            ->innerJoin('profile', 'profile.user_id = order_chat.sent_by_id')
            ->innerJoin('organization', 'organization.id = user.organization_id')
            ->innerJoin('order', '`order`.id = order_chat.order_id and `order`.client_id = sender.organization_id OR `order`.vendor_id = sender.organization_id')
            ->orderBy(['created_at' => SORT_DESC]);
         
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
        
        if($params->type == OrderChat::TYPE_DIALOGS)
            $query->andWhere('order_chat.id in (
                        SELECT order_chat.id 
                        FROM order_chat
                        INNER JOIN (
                          SELECT order_id, MAX(created_at) AS created_at
                          FROM order_chat GROUP BY order_id
                        ) AS max USING (order_id, created_at))');

        if(isset($params->count))
        {
        $query->limit($params->count);
            if(isset($params->page))
            {
                $offset = ($params->page * $params->count) - $params->count;
                $query->offset($offset);
            }
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
    
    public function actionCreate() {
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');
            return (OrderChat::sendChatMessage($user, $order_id, $message)) ? "success" : "fail";
        }else
        {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }

    public function actionViewed() {
        if (Yii::$app->request->post() && Yii::$app->request->post('message_id')) {
            $message = OrderChat::findOne(['id'=>Yii::$app->request->post('message_id')]);
            if($message == mull)
                return;
                $message->viewed = 1;
                $message->save();
        }else
        {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }
}
