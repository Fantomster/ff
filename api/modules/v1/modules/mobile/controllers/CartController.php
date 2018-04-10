<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\RelationUserOrganization;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\models\OrderChat;
use common\models\search\OrderContentSearch;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CartController extends ActiveController {

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
            /*'view' => [
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
            ],*/
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


        $currencyTable = \common\models\Currency::tableName();
        $orderTable = \common\models\Order::tableName();
        
        $query->select("$orderTable.*, $currencyTable.symbol as symbol");
        $query->leftJoin($currencyTable,"$currencyTable.id = $orderTable.currency_id");
        $query->andWhere('status = 7');
        
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

    public function actionAddToCart() {
        $post = Yii::$app->request->post();
        //var_dump($post);
        $quantity = $post['quantity'];
        if ($quantity <= 0) {
            return "error";
        }
        $client = Yii::$app->user->getIdentity()->organization;
        $orders = $client->getCart();

        $product = CatalogGoods::findOne(['base_goods_id' => $post['id'], 'cat_id' => $post['cat_id']]);

        if ($product) {
            $product_id = $product->baseProduct->id;
            $price = $product->price;
            $product_name = $product->baseProduct->product;
            $vendor = $product->organization;
            $units = $product->baseProduct->units;
            $article = $product->baseProduct->article;
        } else {
            $product = CatalogBaseGoods::findOne(['id' => $post['id'], 'cat_id' => $post['cat_id']]);
            if (!$product) {
                return "error";
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $vendor = $product->vendor;
            $units = $product->units;
            $article = $product->article;
        }
        $isNewOrder = true;

        foreach ($orders as $order) {
            if ($order->vendor_id == $vendor->id) {
                $isNewOrder = false;
                $alteringOrder = $order;
            }
        }
        if ($isNewOrder) {
            $newOrder = new Order();
            $newOrder->client_id = $client->id;
            $newOrder->vendor_id = $vendor->id;
            $newOrder->status = Order::STATUS_FORMING;
            $newOrder->currency_id = $product->catalog->currency_id;
            $newOrder->save();
            $alteringOrder = $newOrder;
        }

        $isNewPosition = true;
        foreach ($alteringOrder->orderContent as $position) {
            if ($position->product_id == $product_id) {
                $position->quantity += $quantity;
                $position->save();
                $isNewPosition = false;
            }
        }
        if ($isNewPosition) {
            $position = new OrderContent();
            $position->order_id = $alteringOrder->id;
            $position->product_id = $product_id;
            $position->quantity = $quantity;
            $position->price = $price;
            $position->product_name = $product_name;
            $position->units = $units;
            $position->article = $article;
            $position->save();
        }
        $alteringOrder->calculateTotalPrice();
        $cartCount = $client->getCartCount();
        //$this->sendCartChange($client, $cartCount);

        return "success"; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionCheckout() {
        $client = Yii::$app->user->getIdentity()->organization;
        $totalCart = 0;

        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => Yii::t('message', 'frontend.controllers.order.changes_saved', ['ru' => "Изменения сохранены!"]), "type" => "success"];
        }

        $orders = $client->getCart();
        foreach ($orders as $order) {
            $order->calculateTotalPrice();
            $totalCart += $order->total_price;
        }

        return compact('orders', 'totalCart');
    }

    public function actionSetDelivery() {
        if (Yii::$app->request->post()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $client = Yii::$app->user->getIdentity()->organization;
            $order_id = Yii::$app->request->post('order_id');
            $delivery_date = Yii::$app->request->post('delivery_date');
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $oldDateSet = isset($order->requested_delivery);
            if ($order && !empty($delivery_date)) {

                $nowTS = time();
                $requestedTS = strtotime($delivery_date . ' 19:00:00');

                $timestamp = date('Y-m-d H:i:s', strtotime($delivery_date . ' 19:00:00'));

                if ($nowTS < $requestedTS) {
                    $order->requested_delivery = $timestamp;
                    $order->save();
                } else {
                    $result = ["message" => Yii::t('message', 'frontend.controllers.order.uncorrect_date', ['ru' => "Некорректная дата"]), "result" => "error"];
                    return $result;
                }
            }
            if ($oldDateSet && !empty($delivery_date)) {
                $result = ["message" => Yii::t('message', 'frontend.controllers.order.date_changed', ['ru' => "Дата доставки изменена"]), "result" => "success"];
                return $result;
            }
            if (!$oldDateSet && !empty($delivery_date)) {
                $result = ["message" => Yii::t('message', 'frontend.controllers.order.date_set', ['ru' => "Дата доставки установлена"]), "result" => "success"];
                return $result;
            }
            if (empty($delivery_date)) {
                $order->requested_delivery = null;
                $order->save();
                $result = ["message" => Yii::t('message', 'frontend.controllers.order.seted_date', ['ru' => "Дата доставки удалена"]), "result" => "success"];
                return $result;
            }
        }
    }

    public function actionSetNote($order_content_id) {

        $client = Yii::$app->user->getIdentity()->organization;

        if (Yii::$app->request->post()) {
            $orderContent = OrderContent::find()->where(['id' => $order_content_id])->one();
            if ($orderContent) {
                $order = $orderContent->order;

                if ($order && $order->client_id == $client->id && $order->status == Order::STATUS_FORMING) {
                    $orderContent->comment = Yii::$app->request->post('comment');
                    $orderContent->save();
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    $result = [ "comment" => $orderContent->comment, "result" => "success"];
                    return $result;
                }
            }
        }
        $result = ["comment" => $orderContent->comment, "result" => "error"];
        return $result;
    }

    public function actionSetComment($order_id) {

        $client = Yii::$app->user->getIdentity()->organization;

        if (Yii::$app->request->post()) {
//            $order_id = Yii::$app->request->post('order_id');
            $order = Order::find()->where(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            if ($order) {
                $order->comment = Yii::$app->request->post('comment');
                $order->save();
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["comment" => $order->comment, "result" => "success"];
            }
            return ["comment" => $order->comment, "result" => "error"];

        }
    }

    public function actionRemovePosition($vendor_id, $product_id) {

        $client = Yii::$app->user->getIdentity()->organization;

        $orderDeleted = false;
        $order = Order::find()->where(['vendor_id' => $vendor_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
        foreach ($order->orderContent as $position) {
            if ($position->product_id == $product_id) {
                $position->delete();
            }
            if (!($order->positionCount)) {
                $orderDeleted = $order->delete();
            }
        }
        if (!$orderDeleted) {
            $order->calculateTotalPrice();
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return "success";
    }

    public function actionMakeOrder() {
        $client = Yii::$app->user->getIdentity()->organization;
        $cartCount = $client->getCartCount();

        if (!$cartCount) {
            return false;
        }

        if (Yii::$app->request->post()) {
            if (!Yii::$app->request->post('all')) {
                $order_id = Yii::$app->request->post('id');
                $orders[] = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            }
            foreach ($orders as $order) {
                $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $order->created_by_id = Yii::$app->user->getIdentity()->id;
                $order->created_at = gmdate("Y-m-d H:i:s");
                $order->calculateTotalPrice(); //also saves order
                $this->sendNewOrder($order->vendor);
                $this->sendOrderCreated(Yii::$app->user->getIdentity(), $order);
            }
            $cartCount = $client->getCartCount();
            $this->sendCartChange($client, $cartCount);
            return "success";
        }

        return "error";
    }

    public function actionDeleteOrder($all, $order_id = null) {
        $client = Yii::$app->user->getIdentity()->organization;

        if (!$all) {
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            if ($order) {
                OrderContent::deleteAll(['order_id' => $order->id]);
                $order->delete();
            }
        } else {
            $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            foreach ($orders as $order) {
                OrderContent::deleteAll(['order_id' => $order->id]);
                $order->delete();
            }
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);
        return "success";
    }

    private function sendCartChange($client, $cartCount) {
        $clientUsers = $client->users;

        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $cartCount, 'channel' => $channel, 'isSystem' => 2])
            ]);
        }

        return true;
    }

    private function saveCartChanges($content) {
        foreach ($content as $position) {
            $product = OrderContent::findOne(['id' => $position['id']]);
            if ($product->quantity == 0) {
                $product->delete();
            } else {
                $product->quantity = $position['quantity'];
                $product->save();
            }
        }
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
    public function checkAccess($action, $model = null, $params = []) {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
            $user = Yii::$app->user->identity;

            if (($model->client_id !== $user->organization_id) && ($model->vendor_id !== $user->organization_id))
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }
    
    /**
     * Sends mail informing both sides about new order
     * 
     * @param Organization $sender
     * @param Order $order
     */
    private function sendOrderCreated($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "Новый заказ №" . $order->id . "!";

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $test = $order->recipientsList;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)->id) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_created)
                {
                $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
                }

            $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

            $notification = ($recipient->getSmsNotification($order->vendor_id)->id) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($profile->phone && $notification->order_created)
            {
                //$text = $order->client->name . " сформировал для Вас заказ в системе №" . $order->id;
                $text = "Новый заказ от " . $senderOrg->name . ' ' . Yii::$app->google->shortUrl($order->getUrlForUser($recipient)); //$order->client->name . " сформировал для Вас заказ в системе №" . $order->id;
                $target = $profile->phone;
                Yii::$app->sms->send($text, $target);
            }
        }
    }

    private function sendNewOrder($vendor) {
        $vendorUsers = $vendor->users;

        foreach ($vendorUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['channel' => $channel, 'isSystem' => 3])
            ]);
        }

        return true;
    }
}
