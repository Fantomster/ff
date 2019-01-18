<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\Order;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\OrderChat;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

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
            'index'   => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view'    => [
                'class'      => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel'  => [$this, 'findModel']
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

        $query = "SELECT 
                    order_chat.*, profile.full_name, 
                    organization.name AS organization_name, 
                    organization.picture AS organization_picture 
                    FROM order_chat 
                    INNER JOIN user ON user.id = order_chat.sent_by_id
                    INNER JOIN profile ON profile.user_id = order_chat.sent_by_id 
                    INNER JOIN organization ON organization.id = user.organization_id 
                    INNER JOIN " . Order::tableName() . " o ON o.id = order_chat.order_id and (o.client_id = " . Yii::$app->user->identity->organization_id . " OR o.vendor_id = " . Yii::$app->user->identity->organization_id . ")";

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return new SqlDataProvider([
                'sql'        => $query . " ORDER BY created_at DESC",
                'pagination' => false,
            ]);
        }

        $filters = [];
        if ($params->type == OrderChat::TYPE_DIALOGS)
            $filters[] = ' order_chat.id in (
                        SELECT order_chat.id 
                        FROM order_chat
                        INNER JOIN (
                          SELECT order_id, MAX(created_at) AS created_at
                          FROM order_chat GROUP BY order_id
                        ) AS max USING (order_id, created_at))';

        $filter = (isset($params->id)) ? "id = $params->id" : null;
        if ($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->order_id)) ? "order_id = $params->order_id" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->is_system)) ? "is_system = $params->is_system" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->message)) ? "message = $params->message" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->created_at)) ? "created_at = $params->created_at" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->viewed)) ? "viewed = $params->viewed" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->recipient_id)) ? "recipient_id = $params->recipient_id" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = (isset($params->danger)) ? "danger = $params->danger" : null;
        if($filter != null)
            $filters[] = $filter;

        $filter = implode(" AND ", $filters);

        if (strlen($filter) > 0)
            $query .= " WHERE " . $filter;

        $query .= " ORDER BY created_at DESC";
        if (isset($params->count)) {
            $query .= " LIMIT " . $params->count;
            if (isset($params->page)) {
                $offset = ($params->page * $params->count) - $params->count;
                $query .= " OFFSET ".$offset;
            }
        }

        $dataProvider = new SqlDataProvider([
            'sql'        => $query,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    public function actionCreate() {
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');
            return (OrderChat::sendChatMessage($user, $order_id, $message)) ? "success" : "fail";
        } else {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }

    public function actionViewed() {
        if (Yii::$app->request->post() && Yii::$app->request->post('message_id')) {
            $message = OrderChat::findOne(['id' =>Yii::$app->request->post('message_id')]);
            if($message == mull)
                return;
            $message->viewed = 1;
            $message->save();
        } else {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }
}
