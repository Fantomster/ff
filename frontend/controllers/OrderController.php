<?php

namespace frontend\controllers;

use Yii;
use common\models\search\OrderCatalogSearch;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\Order;
use common\models\Role;
use common\models\OrderContent;
use common\models\Organization;
use common\models\search\OrderSearch;
use common\models\search\OrderContentSearch;
use yii\helpers\Json;
use common\models\OrderChat;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\web\HttpException;

class OrderController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => [
                    'index',
                    'view',
                    'create',
                    'send-message',
                    'ajax-add-to-cart',
                    'ajax-clear-order',
                    'ajax-make-order',
                    'ajax-order-action',
                    'ajax-order-refresh',
                    'ajax-refresh-buttons',
                    'ajax-show-order',
                ],
                'rules' => [
//                    [
//                        'allow' => false,
//                        'roles' => ['?'],
//                        'actions' => [
//                            'index',
//                            'view',
//                            'create',
//                            'send-message',
//                            'ajax-add-to-cart',
//                            'ajax-categories',
//                            'ajax-clear-order',
//                            'ajax-make-order',
//                            'ajax-modify-cart',
//                            'ajax-order-action',
//                            'ajax-order-refresh',
//                            'ajax-refresh-buttons',
//                            'ajax-show-order',
//                            'ajax-vendors',
//                        ],
//                    ],
                    [
                        'actions' => ['index', 'view', 'send-message', 'ajax-order-action', 'ajax-refresh-buttons',],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                            'ajax-add-to-cart',
                            'ajax-clear-order',
                            'ajax-make-order',
                            'ajax-modify-cart',
                            'ajax-order-refresh',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                        ],
                    ],
                ],
                'denyCallback' => function($rule, $action) {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
            ],
        ];
    }

    public function actionCreate() {

        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearch();
        $params = Yii::$app->request->getQueryParams();

        $selectedCategory = null;
        $selectedVendor = null;
        $searchString = '';

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = ($selectedCategory == $params['OrderCatalogSearch']['selectedCategory']) ? $params['OrderCatalogSearch']['selectedVendor'] : '';
            $selectedCategory = $params['OrderCatalogSearch']['selectedCategory'];
        }

        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        $dataProvider = $searchModel->search($params);

        $orders = $client->getCart();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('create', compact('dataProvider', 'searchModel', 'orders', 'client', 'vendors'));
        } else {
            return $this->render('create', compact('dataProvider', 'searchModel', 'orders', 'client', 'vendors'));
        }
    }

    public function actionAjaxAddToCart() {

        $client = $this->currentUser->organization;
        $orders = $client->getCart();

        $post = Yii::$app->request->post();
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
                return true;//$this->renderAjax('_orders', compact('orders'));
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $vendor = $product->vendor;
            $units = $product->units;
            $article = $product->article;
        }
        $quantity = (int) $post['quantity'];
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
        //$orders = $client->getCart();

        return true;//$this->renderPartial('_orders', compact('orders'));
    }

    public function actionAjaxRemovePosition() {

        $client = $this->currentUser->organization;
        $post = Yii::$app->request->post();

        if ($post && $post['vendor_id'] && $post['product_id']) {
            $order = Order::find()->where(['vendor_id' => $post['vendor_id'], 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $post['product_id']) {
                    $position->delete();
                }
                $test = $order->positionCount;
                if (!($order->positionCount)) {
                    $order->delete();
                }
            }
        }

        //$orders = $client->getCart();

        return true;//$this->renderPartial('_orders', compact('orders'));
    }

    public function actionAjaxChangeQuantity($vendor_id = null, $product_id = null) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $quantity = Yii::$app->request->post('quantity');
            $product_id = Yii::$app->request->post('product_id');
            $vendor_id = Yii::$app->request->post('vendor_id');
            $order = Order::find()->where(['vendor_id' => Yii::$app->request->post('vendor_id'), 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $position->quantity = $quantity;
                    $position->save();
                }
            }
            //$orders = $client->getCart();
            return true;//$this->renderPartial('_orders', compact('orders'));
        }
        
        if (Yii::$app->request->get()) {
            $order = Order::findOne(['vendor_id' => $vendor_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $quantity = $position->quantity;
                    $product_name = $position->product_name;
                }
            }
            return $this->renderAjax('_change-quantity', compact('vendor_id', 'product_id', 'quantity', 'product_name'));
        }
    }

    public function actionAjaxMakeOrder($all = false) {
        $client = $this->currentUser->organization;
        
        if (Yii::$app->request->post()) {
            if (!$all) {
                $order_id = Yii::$app->request->post('id');
                $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                if ($order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->save();
                }
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                foreach ($orders as $order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->save();
                }
            }
        }
    }

    public function actionRefreshCart() {
        $client = $this->currentUser->organization;
        $orders = $client->getCart();
        return $this->renderAjax('_cart', compact('orders'));
    }
    
    public function actionIndex() {
        $searchModel = new OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $params = Yii::$app->request->getQueryParams();
        $organization = $this->currentUser->organization;
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $params['OrderSearch']['client_search_id'] = $this->currentUser->organization_id;
            $newCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
            $processingCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
            $fulfilledCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
            $query = Yii::$app->db->createCommand('select sum(total_price) as total from `order` where status=' . Order::STATUS_DONE . ' and client_id=' . $organization->id)->queryOne();
            $totalPrice = $query['total'];
        } else {
            $params['OrderSearch']['vendor_search_id'] = $this->currentUser->organization_id;
            $newCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
            $processingCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
            $fulfilledCount = Order::find([])->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
            $query = Yii::$app->db->createCommand('select sum(total_price) as total from `order` where status=' . Order::STATUS_DONE . ' and vendor_id=' . $organization->id . ';')->queryOne();
            $totalPrice = $query['total'];
        }
        $dataProvider = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        } else {
            return $this->render('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        }
    }

    public function actionView($id) {
        $order = Order::findOne(['id' => $id]);
        $user = $this->currentUser;
        if (!(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            throw new \yii\web\HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_SUPPLIER)) {
            $this->redirect(['/order/index']);
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_RESTAURANT)) {
            $this->redirect(['/order/checkout']);
        }
        $organizationType = $user->organization->type_id;
        if (isset($_POST['hasEditable'])) {
            $model = OrderContent::findOne(['id' => Yii::$app->request->post('editableKey')]);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $posted = current($_POST['OrderContent']);
            $post = ['OrderContent' => $posted];
            $allowedStatuses = [
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_PROCESSING
            ];
            if ($model->load($post) && in_array($order->status, $allowedStatuses)) {
                $quantityChanged = isset($posted['quantity']);
                if (!$quantityChanged && ($order->status == Order::STATUS_PROCESSING)) {
                    return ['output' => '', 'message' => ''];
                }
                $value = ($quantityChanged) ? $model->quantity : $model->price;
                $model->save();
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $order->status = $order->status == Order::STATUS_PROCESSING ? $order->status == Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    if ($quantityChanged) {
                        $this->sendSystemMessage($user->id, $order->id, 'Клиент изменил количество товара ' . $model->product->product . ' на ' . $model->quantity);
                    }
                } else {
                    $order->status = $order->status == Order::STATUS_PROCESSING ? $order->status == Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                    if ($quantityChanged) {
                        $this->sendSystemMessage($user->id, $order->id, 'Поставщик изменил количество товара ' . $model->product->product . ' на ' . $model->quantity);
                    } else {
                        $this->sendSystemMessage($user->id, $order->id, 'Поставщик изменил цену товара ' . $model->product->product . ' на ' . $model->price);
                    }
                }
                $totalPrice = 0;
                foreach ($order->orderContent as $position) {
                    $totalPrice += ($position->price * $position->quantity);
                }
                $order->total_price = $totalPrice;
                $order->save();
                return ['output' => $value, 'message' => '', 'buttons' => $this->renderPartial('_order-buttons', compact('order', 'organizationType'))];
            } else {
                return ['output' => '', 'message' => ''];
            }
        }

        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        return $this->render('view', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
    }

    public function actionCheckout() {
        $client = $this->currentUser->organization;
        $orders = $client->getCart();

        if (isset($_POST['hasEditable'])) {
            $model = OrderContent::findOne(['id' => Yii::$app->request->post('editableKey')]);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $posted = current($_POST['OrderContent']);
            $post = ['OrderContent' => $posted];
            if ($model->load($post)) {
                $model->save();
                $order = $model->order;
                $totalPrice = 0;
                foreach ($order->orderContent as $position) {
                    $totalPrice += ($position->price * $position->quantity);
                }
                $order->total_price = $totalPrice;
                $order->save();
                return [
                    'output' => $model->quantity, 
                    'message' => '', 
                    'positionTotal' => $model->price * $model->quantity, 
                    'positionId' => $model->id, 
                    'orderId' => $order->id,
                    'orderTotal' => $totalPrice
                        ];
            } else {
                return ['output' => '', 'message' => ''];
            }
        }
        
        return $this->render('checkout', compact('orders'));
    }

    public function actionAjaxOrderAction() {
        if (Yii::$app->request->post()) {
            $user_id = $this->currentUser->id;
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            switch (Yii::$app->request->post('action')) {
                case 'cancel':
                    $order->status = ($organizationType == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                    $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? 'Клиент' : 'Поставщик';
                    $systemMessage = $initiator . ' отменил заказ!';
                    break;
                case 'confirm':
                    if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT)) {
                        $order->status = Order::STATUS_PROCESSING;
                        $order->accepted_by_id = $user_id;
                        $systemMessage = 'Клиент подтвердил заказ!';
                    } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR)) {
                        $systemMessage = 'Поставщик подтвердил заказ!';
                        $order->status = Order::STATUS_PROCESSING;
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                        $systemMessage = 'Клиент получил заказ!';
                        $order->status = Order::STATUS_DONE;
                    }
                    break;
            }
            if ($order->save()) {
                $this->sendSystemMessage($user_id, $order->id, $systemMessage);
                return $this->renderPartial('_order-buttons', compact('order', 'organizationType'));
            }
        }
    }

    public function actionSendMessage() {
        $user = $this->currentUser;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $name = $user->profile->full_name;
            $message = Yii::$app->request->post('message');
            $channel = 'order' . Yii::$app->request->post('order_id');
            $newMessage = new OrderChat();
            $newMessage->order_id = Yii::$app->request->post('order_id');
            $newMessage->sent_by_id = $user->id;
            $newMessage->message = $message;
            $newMessage->save();

            $body = $this->renderPartial('_chat-message', [
                'name' => $name,
                'message' => $newMessage->message,
                'time' => $newMessage->created_at,
                'isSystem' => 0,
            ]);

            return Yii::$app->redis->executeCommand('PUBLISH', [
                        'channel' => 'chat',
                        'message' => Json::encode(['body' => $body, 'channel' => $channel, 'isSystem' => 0])
            ]);
        }
    }

    public function actionAjaxRefreshButtons() {
        if (Yii::$app->request->post()) {
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            return $this->renderPartial('_order-buttons', compact('order', 'organizationType'));
        }
    }

    private function sendSystemMessage($user_id, $order_id, $message) {
        $channel = 'order' . $order_id;
        $newMessage = new OrderChat();
        $newMessage->order_id = $order_id;
        $newMessage->message = $message;
        $newMessage->is_system = 1;
        $newMessage->sent_by_id = $user_id;
        $newMessage->save();
        $body = $this->renderPartial('_chat-message', ['name' => '', 'message' => $newMessage->message, 'time' => $newMessage->created_at, 'isSystem' => 1]);

        return Yii::$app->redis->executeCommand('PUBLISH', [
                    'channel' => 'chat',
                    'message' => Json::encode(['body' => $body, 'channel' => $channel, 'isSystem' => 1])
        ]);
    }

}
