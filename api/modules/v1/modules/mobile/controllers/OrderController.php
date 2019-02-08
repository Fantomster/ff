<?php

namespace api\modules\v1\modules\mobile\controllers;

use api_web\components\Registry;
use common\models\OrderStatus;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use common\models\Organization;
use common\models\OrderChat;
use common\models\search\OrderContentSearch;
use yii\helpers\Json;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderController extends ActiveController
{

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Order';

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
            'index'  => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view'   => [
                'class'      => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel'  => [$this, 'findModel']
            ],
            /*'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\Order',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],*/
            'delete' => [
                'class'       => 'yii\rest\DeleteAction',
                'modelClass'  => 'common\models\Order',
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
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

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
        ]);
        $filters = [];
        $user = Yii::$app->user->getIdentity();

        $filters['client_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->organization_id : $params->client_id;
        $filters['vendor_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->vendor_id;

        $currencyTable = \common\models\Currency::tableName();
        $orderTable = \common\models\Order::tableName();
        $organizationTable = Organization::tableName();

        $query->select("$orderTable.*, $currencyTable.symbol as symbol, client_org.name as client_name, vendor_org.name as vendor_name ");
        $query->leftJoin($currencyTable, "$currencyTable.id = $orderTable.currency_id");
        $query->leftJoin("$organizationTable as client_org", "client_org.id = $orderTable.client_id");
        $query->leftJoin("$organizationTable as vendor_org", "vendor_org.id = $orderTable.vendor_id");

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }

        if (isset($params->count)) {
            $query->limit($params->count);
            if (isset($params->page)) {
                $offset = ($params->page * $params->count) - $params->count;
                $query->offset($offset);
            }
        }

        $filters['order.id'] = $params->id;
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

    public function actionNewOrder()
    {
        $post = Yii::$app->request->post();
        $user = Yii::$app->user->getIdentity();
        $res = [];

        $newOrder = new \common\models\Order();
        $newOrder->load($post, 'Order');
        $min_delivery = $newOrder->vendor->delivery->min_order_price;

        if ($newOrder->total_price < $min_delivery) {
            echo "Total price is less than the minimum";
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (isset($post['GoodsNotes'])) {
                foreach ($post['GoodsNotes'] as $note) {
                    if (empty($note))
                        break;
                    $notes = \common\models\GoodsNotes::find()->where('catalog_base_goods_id = :prod_id and rest_org_id = :org_id', [':prod_id' => $note['catalog_base_goods_id'], ':org_id' => $user->organization_id])->one();

                    if ($notes == null) {
                        $notes = new \common\models\GoodsNotes();
                        $notes->attributes = $note;
                        $notes->catalog_base_goods_id = $note['catalog_base_goods_id'];
                        $notes->rest_org_id = $user->organization_id;
                        unset($notes->id);
                    } else {
                        $notes->note = $note['note'];
                        $notes->created_at = $note['created_at'];
                        if (array_key_exists('updated_at', $note) != null)
                            $notes->updated_at = $note['updated_at'];
                    }

                    if (!$notes->save()) {
                        echo json_encode(['GoodsNotes' => $notes->getErrors()]);
                        $transaction->rollback();
                        return;
                    }
                    $notes->created_at = $note['created_at'];
                    if (array_key_exists('updated_at', $note) != null)
                        $notes->updated_at = $note['updated_at'];
                    $res[] = $notes;
                }
            }

            $newOrder->status = OrderStatus::STATUS_FORMING;
            $newOrder->service_id = Registry::MC_BACKEND;
            $newOrder->currency_id = 1;
            if (!$newOrder->save()) {
                echo json_encode(['Order' => $newOrder->getErrors()]);
                $transaction->rollback();
                return;
            }

            foreach ($post['OrderContents'] as $position) {
                $pos = new \common\models\OrderContent();
                $pos->attributes = $position;
                unset($pos->id);
                $pos->order_id = $newOrder->id;
                if (!$pos->save()) {
                    var_dump($pos->getErrors());
                    echo json_encode(['OrderContents' => $pos->getErrors()]);
                    $transaction->rollback();
                    return;
                }
            }

            $newOrder->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            if (!$newOrder->save()) {
                echo json_encode(['Order' => $newOrder->getErrors()]);
                $transaction->rollback();
                return;
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
            throw new BadRequestHttpException($ex);
        }

        $OrderContents = $newOrder->orderContent;

        $contents = [];

        foreach ($OrderContents as $item) {
            $contents[] = $item;
        }
        $OrderContents = $contents;
        $Order = \common\models\Order::findOne(['id' => $newOrder->id]);
        $GoodsNotes = $res;
        /* $result = [ 'Order'=> compact('Order'),
          'OrderContents' => compact('OrderContents'),
          'GoodsNotes' => compact('GoodsNotes')
          ] */
        $this->sendOrderCreated($user, $Order);
        return compact('Order', 'OrderContents', 'GoodsNotes');
    }

    public function actionUpdate($id)
    {
        $model = Order::findOne(['id' => $id]);
        $status = $model->status;
        $this->checkAccess($model->id, $model);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        if (($status <> $model->status) && ($model->status == OrderStatus::STATUS_DONE)) {
            $currentUser = Yii::$app->user->getIdentity();
            $systemMessage = $model->client->name . ' получил заказ!';
            $model->actual_delivery = gmdate("Y-m-d H:i:s");
            $this->sendOrderDone($model->createdBy, $model);
            if ($model->save()) {
                $this->sendSystemMessage($currentUser, $model->id, $systemMessage, false);
                return ["title" => $systemMessage, "type" => "success"];
            }
        }
        return $model;
    }

    public function actionCancelOrder()
    {

        $user = Yii::$app->user->getIdentity();
        $initiator = $user->organization;

        if (Yii::$app->request->post()) {
            $order_id = Yii::$app->request->post('id');
            switch ($initiator->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $order = Order::find()->where(['id' => $order_id, 'client_id' => $initiator->id])->one();
                    break;
                case Organization::TYPE_SUPPLIER:
                    $order = $this->findOrder([Order::tableName() . '.id' => $order_id, 'vendor_id' => $initiator->id], Yii::$app->user->can('manage'));
                    break;
            }
            if ($order) {
                if (Yii::$app->request->post("comment")) {
                    $order->comment = Yii::$app->request->post("comment");
                }
                $order->status = ($initiator->type_id == Organization::TYPE_RESTAURANT) ? OrderStatus::STATUS_CANCELLED : OrderStatus::STATUS_REJECTED;
                $systemMessage = $initiator->name . ' отменил заказ!';
                $danger = true;
                $order->save();
                if ($initiator->type_id == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
                $this->sendSystemMessage($user, $order->id, $systemMessage, $danger);
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => "Заказ успешно отменен!", "type" => "success"];
            }
            return false;
        }
    }

    public function actionConfirmOrder()
    {
        if (Yii::$app->request->post()) {
            $currentUser = Yii::$app->user->getIdentity();
            $user_id = $currentUser->id;

            $order = Order::findOne(['id' => Yii::$app->request->post('id')]);
            $organizationType = $currentUser->organization->type_id;
            $danger = false;
            $edit = false;
            $systemMessage = '';
            /*if ($order->isObsolete) {
                $systemMessage = $order->client->name . ' получил заказ!';
                $order->status = Order::STATUS_DONE;
                $order->actual_delivery = gmdate("Y-m-d H:i:s");
                $this->sendOrderDone($order->createdBy, $order);
            } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT)) {
                $order->status = Order::STATUS_PROCESSING;
                $systemMessage = $order->client->name . ' подтвердил заказ!';
                $this->sendOrderProcessing($order->client, $order);
                $edit = true;
            } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR)) {
                $systemMessage = $order->vendor->name . ' подтвердил заказ!';
                $order->accepted_by_id = $user_id;
                $order->status = Order::STATUS_PROCESSING;
                $edit = true;
                $this->sendOrderProcessing($order->vendor, $order);
            } elseif (*/

            if ($organizationType == Organization::TYPE_RESTAURANT && $order->status < 4) {
                $systemMessage = $order->client->name . ' получил заказ!';
                $order->status = OrderStatus::STATUS_DONE;
                $order->actual_delivery = gmdate("Y-m-d H:i:s");
                $this->sendOrderDone($order->createdBy, $order);
            }

            if (!empty($systemMessage))
                if ($order->save()) {
                    $this->sendSystemMessage($currentUser, $order->id, $systemMessage, $danger);
                    return ["title" => $systemMessage, "type" => "success"];
                }
        }
    }

    /**
     * Checks the privilege of the current user.
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string          $action the ID of the action to be executed
     * @param \yii\base\Model $model  the model to be accessed. If `null`, it means no specific model is being accessed.
     * @param array           $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
            $user = Yii::$app->user->identity;

            if (($model->client_id !== $user->organization_id) && ($model->vendor_id !== $user->organization_id))
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }

    /**
     * Sends mail informing both sides that order is delivered and accepted
     *
     * @param User  $sender
     * @param Order $order
     */
    private function sendOrderDone($sender, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@common/mail/layouts/order';
        // send email
        $senderOrg = $sender->organization;
        $subject = "Заказ №" . $order->id . " выполнен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_done) {
                        $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }

                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($profile->phone && $notification->order_done) {
                        $text = $order->vendor->name . " выполнил заказ " . Yii::$app->google->shortUrl($order->getUrlForUser($recipient));//$order->vendor->name . " выполнил заказ в системе №" . $order->id;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides that vendor confirmed order
     *
     * @param Organization $senderOrg
     * @param Order        $order
     */
    private function sendOrderProcessing($senderOrg, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@common/mail/layouts/order';
        // send email
        $subject = "Заказ №" . $order->id . " подтвержден!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->getEmailNotification($order->vendor_id)->order_processing) {
                $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
            }
            $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

            if ($profile->phone && $recipient->getSmsNotification($order->vendor_id)->order_created) {
                $text = "Заказ у " . $order->vendor->name . " согласован " . Yii::$app->google->shortUrl($order->getUrlForUser($recipient));//"Заказ в системе №" . $order->id . " согласован.";
                $target = $profile->phone;
                Yii::$app->sms->send($text, $target);
            }
        }
    }

    /**
     * Sends mail informing both sides about new order
     *
     * @param Organization $sender
     * @param Order        $order
     */
    private function sendOrderCreated($sender, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@common/mail/layouts/order';
        // send email
        $senderOrg = $sender->organization;
        $subject = "Новый заказ №" . $order->id . "!";

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $test = $order->recipientsList;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_created) {
                        $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }

                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

                $notification = $recipient->getSmsNotification($orgs);
                if ($notification)
                    if ($profile->phone && $notification->order_created) {
                        //$text = $order->client->name . " сформировал для Вас заказ в системе №" . $order->id;
                        $text = "Новый заказ от " . $senderOrg->name . ' ' . Yii::$app->google->shortUrl($order->getUrlForUser($recipient)); //$order->client->name . " сформировал для Вас заказ в системе №" . $order->id;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides about cancellation of order
     *
     * @param Organization $senderOrg
     * @param Order        $order
     */
    private function sendOrderCanceled($senderOrg, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@common/mail/layouts/order';
        // send email
        $subject = "Заказ №" . $order->id . " отменен!";

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_canceled) {
                        $notification = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }

                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($profile->phone && $notification->order_canceled) {
                        $text = $senderOrg->name . " отменил заказ " . Yii::$app->google->shortUrl($order->getUrlForUser($recipient)); //$senderOrg->name . " отменил заказ в системе №" . $order->id;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
            }
        }
    }

    private function sendSystemMessage($user, $order_id, $message, $danger = false)
    {
        $order = Order::findOne(['id' => $order_id]);

        $newMessage = new OrderChat();
        $newMessage->order_id = $order_id;
        $newMessage->message = $message;
        $newMessage->is_system = 1;
        $newMessage->sent_by_id = $user->id;
        $newMessage->danger = $danger;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        $newMessage->save();
        $body = $this->renderPartial('@frontend/views/order/_chat-message', [
            'name'      => '',
            'message'   => $newMessage->message,
            'time'      => $newMessage->created_at,
            'isSystem'  => 1,
            'sender_id' => $user->id,
            'ajax'      => 1,
            'danger'    => $danger,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body'     => $body,
                    'channel'  => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }
        foreach ($vendorUsers as $vendorUser) {
            $channel = 'user' . $vendorUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body'     => $body,
                    'channel'  => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
    }

}
