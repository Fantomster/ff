<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\common\models\Profile;
use api\modules\v1\modules\mobile\resources\Organization;
use api\modules\v1\modules\mobile\resources\User;
use common\models\Order;
use Yii;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\OrderChat;
use yii\data\SqlDataProvider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderChatController extends ActiveController
{

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\OrderChat';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
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
     * @return OrderChat
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = OrderChat::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }

    /**
     * @return SqlDataProvider
     * @throws \Throwable
     */
    public function prepareDataProvider()
    {
        $params = new OrderChat();
        $params->setAttributes(Yii::$app->request->queryParams);

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $org = $user->organization;

        $query = (new Query())
            ->select([
                'oc.*',
                'p.full_name',
                'org.name AS organization_name',
                'org.picture AS organization_picture'
            ])
            ->from(['oc' => OrderChat::tableName()])
            ->innerJoin(['u' => User::tableName()], 'u.id = oc.sent_by_id')
            ->innerJoin(['p' => Profile::tableName()], 'p.user_id = oc.sent_by_id')
            ->innerJoin(['org' => Organization::tableName()], 'org.id = u.organization_id')
            ->innerJoin(['ord' => Order::tableName()], "ord.id = oc.order_id")
            ->where([
                'or',
                ['ord.client_id' => $org->id],
                ['ord.vendor_id' => $org->id]
            ]);

        $query->andFilterWhere([
            'oc.id'        => $params->id,
            'order_id'     => $params->order_id,
            'is_system'    => $params->is_system,
            'viewed'       => $params->viewed,
            'recipient_id' => $params->recipient_id,
            'danger'       => $params->danger,

        ])
            ->andFilterWhere(['>=', 'oc.created_at', $params->created_at])
            ->andFilterWhere(['LIKE', 'message', $params->message]);

        if ($params->type == OrderChat::TYPE_DIALOGS) {
            $dialogs = (new Query())
                ->select('id')
                ->from(OrderChat::tableName())
                ->innerJoin([
                    'max' => (new Query())
                        ->select([
                            'order_id',
                            'MAX(created_at) AS created_at'
                        ])
                        ->from(OrderChat::tableName())
                        ->groupBy('order_id')
                ])
                ->createCommand()
                ->getRawSql();

            $query->andWhere(['IN', 'oc.id', "({$dialogs})"]);
        }

        return new SqlDataProvider([
            'sql'        => $query->orderBy(['created_at' => SORT_DESC])->createCommand()->getRawSql(),
            'pagination' => [
                'page'     => isset($params->page) ? ($params->page - 1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
        ]);
    }

    /**
     * @return string
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');

            return (OrderChat::sendChatMessage($user, $order_id, $message)) ? "success" : "fail";
        } else {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionViewed()
    {
        if (Yii::$app->request->post() && Yii::$app->request->post('message_id')) {
            $message = OrderChat::findOne(['id' => Yii::$app->request->post('message_id')]);
            if ($message == null)

                return;
            $message->viewed = 1;
            $message->save();
        } else {
            throw new \yii\web\BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }
    }
}
