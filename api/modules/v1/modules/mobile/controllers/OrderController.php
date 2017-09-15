<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

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
             'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\Order',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => 'common\models\Order',
                'checkAccess' => [$this, 'checkAccess'],
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
    public function prepareDataProvider() {
        $params = new Order();
        $query = Order::find();

        $dataProvider = new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();

        $filters['client_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->organization_id : $params->client_id;
        $filters['vendor_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->vendor_id;


        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }

         if(isset($params->count))
        {
            $query->limit($params->count);
                if(isset($params->page))
                {
                    $offset = ($params->page * $params->count) - $params->count;
                    $query->offset($offset);
                }
        }

        $filters['id1'] = $params->id;
        $filters['created_by_id'] = $params->created_by_id;
        $filters['accepted_by_id'] = $params->accepted_by_id;
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
        $query->andWhere('status <> 7');

        return $dataProvider;
    }

    public function actionNewOrder() {
        $post = Yii::$app->request->post();
        $user = Yii::$app->user->getIdentity();
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (isset($post['GoodsNotes'])) {
                $res = [];
                foreach ($post['GoodsNotes'] as $note) {
                    if(!isset($note))
                        continue;
                    $notes = \common\models\GoodsNotes::find()->where('catalog_base_goods_id = :prod_id and rest_org_id = :org_id',
                            [':prod_id' => $note['catalog_base_goods_id'], ':org_id' => $user->organization_id])->one();
                    
                    if($notes == null)
                    {
                        $notes = new \common\models\GoodsNotes();
                        $notes->attributes = $note;
                        $notes->catalog_base_goods_id = $note['catalog_base_goods_id'];
                        $notes->rest_org_id =  $user->organization_id;
                        unset($notes->id);
                    }
                    else
                    {
                        $notes->note = $note['note'];
                        $notes->created_at = $note['created_at'];
                        if(array_key_exists('updated_at', $note) != null)
                        $notes->updated_at = $note['updated_at'];
                    }

                    if (!$notes->save()) 
                    {
                        var_dump($notes->getErrors());
                        die();
                        throw new BadRequestHttpException;
                    }
                    $notes->created_at = $note['created_at'];
                    if(array_key_exists('updated_at', $note) != null)
                        $notes->updated_at = $note['updated_at'];
                    $res[] = $notes;
                }
            }
            $newOrder = new \common\models\Order();
            $newOrder->load($post, 'Order');
            $newOrder->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            if (!$newOrder->save())
            {
                var_dump($newOrder->getErrors());
                die();
                throw new BadRequestHttpException;
            }

            foreach ($post['OrderContents'] as $position) {
                $pos = new \common\models\OrderContent();
                $pos->attributes = $position;
                unset($pos->id);
                $pos->order_id = $newOrder->id;
                if (!$pos->save())
                {
                    var_dump($pos->getErrors());
                        die();
                    throw new BadRequestHttpException;
                }
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
            throw new BadRequestHttpException($ex);
        }

        $OrderContents = $newOrder->orderContent;
        $Order = $newOrder;
        $GoodsNotes = $res;
        /*$result = [ 'Order'=> compact('Order'),
                    'OrderContents' => compact('OrderContents'),
                    'GoodsNotes' => compact('GoodsNotes')
            ]*/
        $this->sendOrderCreated($user, $Order);
        return compact('Order', 'OrderContents', 'GoodsNotes');
    }
    
    /**
    * Checks the privilege of the current user.
    *
    * This method should be overridden to check whether the current user has the privilege
    * to run the specified action against the specified data model.
    * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
    *
    * @param string $action the ID of the action to be executed
    * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
    * @param array $params additional parameters
    * @throws ForbiddenHttpException if the user does not have access
    */
   public function checkAccess($action, $model = null, $params = [])
   {
       // check if the user can access $action and $model
       // throw ForbiddenHttpException if access should be denied
       if ($action === 'update' || $action === 'delete') {
           $user = Yii::$app->user->identity;

           if (($model->client_id !== $user->organization_id)&&($model->vendor_id !== $user->organization_id))
               throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
       }
   }
   
    private function sendOrderCreated($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "MixCart: новый заказ №" . $order->id . "!";

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_created) {
                $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_created) {
                $text = $order->client->name . " сформировал для Вас заказ в системе MixCart №" . $order->id;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
        }
    }

}
