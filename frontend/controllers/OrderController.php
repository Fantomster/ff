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
use common\models\GoodsNotes;
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
                    'edit',
                    'create',
                    'checkout',
                    'send-message',
                    'refresh-cart',
                    'ajax-add-to-cart',
                    'ajax-delete-order',
                    'ajax-make-order',
                    'ajax-order-action',
                    'ajax-change-quantity',
                    'ajax-refresh-buttons',
                    'ajax-remove-position',
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'edit', 'send-message', 'ajax-order-action', 'ajax-refresh-buttons',],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                            'checkout',
                            'refresh-cart',
                            'ajax-add-to-cart',
                            'ajax-delete-order',
                            'ajax-make-order',
                            'ajax-change-quantity',
                            'ajax-remove-position',
                            'ajax-show-details',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
//        }
            ],
        ];
    }

    public function actionCreate() {

        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearch();
        $params = Yii::$app->request->getQueryParams();
        if (Yii::$app->request->post("OrderCatalogSearch")) {
            $params['OrderCatalogSearch'] = Yii::$app->request->post("OrderCatalogSearch");
        }

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = $params['OrderCatalogSearch']['selectedVendor'];
            //$selectedVendor = ($selectedCategory == $params['OrderCatalogSearch']['selectedCategory']) ? $params['OrderCatalogSearch']['selectedVendor'] : '';
            $selectedCategory = $params['OrderCatalogSearch']['selectedCategory'];
        }

        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->params['OrderCatalogSearch[searchString]'] = isset($params['OrderCatalogSearch']['searchString']) ? $params['OrderCatalogSearch']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedCategory]'] = $selectedCategory;

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
                return true; //$this->renderAjax('_orders', compact('orders'));
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $vendor = $product->vendor;
            $units = $product->units;
            $article = $product->article;
        }
        $quantity = $post['quantity'];
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
        $alteringOrder->calculateTotalPrice();
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return true; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionAjaxShowDetails() {
        $get = Yii::$app->request->get();
        $productId = $get['id'];
        $catId = $get['cat_id'];
        $product = CatalogGoods::findOne(['base_goods_id' => $productId, 'cat_id' => $catId]);

        if ($product) {
            $baseProduct = $product->baseProduct;
            $price = $product->price;
        } else {
            $baseProduct = CatalogBaseGoods::findOne(['id' => $get['id'], 'cat_id' => $get['cat_id']]);
            $price = $baseProduct->price;
        }
        $vendor = $baseProduct->vendor;

        return $this->renderAjax("_order-details", compact('baseProduct', 'price', 'vendor', 'productId', 'catId'));
    }

    public function actionAjaxRemovePosition() {

        $client = $this->currentUser->organization;
        $post = Yii::$app->request->post();

        if ($post && $post['vendor_id'] && $post['product_id']) {
            $orderDeleted = false;
            $order = Order::find()->where(['vendor_id' => $post['vendor_id'], 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $post['product_id']) {
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
        }

        //$orders = $client->getCart();

        return true; //$this->renderPartial('_orders', compact('orders'));
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
            $order->calculateTotalPrice();
            return true;
        }

        if (Yii::$app->request->get()) {
            $order = Order::findOne(['vendor_id' => $vendor_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $vendor_name = $order->vendor->name;
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $quantity = $position->quantity;
                    $product_name = $position->product_name;
                    $units = $position->units;
                }
            }
            return $this->renderAjax('_change-quantity', compact('vendor_id', 'product_id', 'quantity', 'product_name', 'vendor_name', 'units'));
        }
    }

    public function actionAjaxSetComment($order_id = null) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $order_id = Yii::$app->request->post('order_id');
            $order = Order::find()->where(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            if ($order && $order->load(Yii::$app->request->post())) {
                $order->save();
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $this->successNotify("Комментарий добавлен");
            }
            return false;
        }

        if (Yii::$app->request->get()) {
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            return $this->renderAjax('_add-comment', compact('order'));
        }
    }

    public function actionAjaxCancelOrder($order_id = null) {

        $initiator = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $order_id = Yii::$app->request->post('order_id');
            switch ($initiator->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $order = Order::find()->where(['id' => $order_id, 'client_id' => $initiator->id])->one();
                    break;
                case Organization::TYPE_SUPPLIER:
                    $order = Order::find()->where(['id' => $order_id, 'vendor_id' => $initiator->id])->one();
                    break;
            }
            if ($order && $order->load(Yii::$app->request->post())) {
                $order->status = ($initiator->type_id == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                $systemMessage = $initiator->name . ' отменил заказ!';
                $danger = true;
                $order->save();
                if (isset($order->accepted_by_id)) {
                    $this->sendOrderCanceled($order->createdBy, $order->acceptedBy, $order->id);
                }
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $this->successNotify("Заказ отменен!");
            }
            return false;
        }

        if (Yii::$app->request->get()) {
            switch ($initiator->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $order = Order::find()->where(['id' => $order_id, 'client_id' => $initiator->id])->one();
                    break;
                case Organization::TYPE_SUPPLIER:
                    $order = Order::find()->where(['id' => $order_id, 'vendor_id' => $initiator->id])->one();
                    break;
            }
            return $this->renderAjax('_cancel-order', compact('order'));
        }
    }

    public function actionAjaxSetNote($product_id = null) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post('GoodsNotes');
            $product_id = $post['catalog_base_goods_id'];
            $note = GoodsNotes::findOne(['catalog_base_goods_id' => $product_id, 'rest_org_id' => $client->id]);
            if ($note && $note->load(Yii::$app->request->post())) {
                $note->save();
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $this->successNotify("Комментарий к товару добавлен");
            }
            return false;
        }

        if (Yii::$app->request->get()) {
            $note = GoodsNotes::findOne(['catalog_base_goods_id' => $product_id, 'rest_org_id' => $client->id]);
            if (!$note) {
                $note = new GoodsNotes();
                $note->rest_org_id = $client->id;
                $note->catalog_base_goods_id = $product_id;
                $note->save();
            }
            return $this->renderAjax('_add-note', compact('note'));
        }
    }

    public function actionAjaxMakeOrder() {
        $client = $this->currentUser->organization;
        $cartCount = $client->getCartCount();

        if (!$cartCount) {
            return false;
        }

        if (Yii::$app->request->post()) {
            if (!Yii::$app->request->post('all')) {
                $order_id = Yii::$app->request->post('id');
                $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                if ($order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->created_by_id = $this->currentUser->id;
                    $order->created_at = gmdate("Y-m-d H:i:s");
                    $order->save();
                    $this->sendNewOrder($order->vendor);
                    $this->sendOrderCreated($this->currentUser, $order->vendor, $order->id);
                }
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                foreach ($orders as $order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->created_by_id = $this->currentUser->id;
                    $order->created_at = gmdate("Y-m-d H:i:s");
                    $order->save();
                    $this->sendNewOrder($order->vendor);
                    $this->sendOrderCreated($this->currentUser, $order->vendor, $order->id);
                }
            }
            $cartCount = $client->getCartCount();
            $this->sendCartChange($client, $cartCount);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $this->successNotify("Заказ успешно оформлен");
        }

        return false;
    }

    public function actionAjaxDeleteOrder() {
        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            if (!Yii::$app->request->post('all')) {
                $order_id = Yii::$app->request->post('id');
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
            return true;
        }

        return false;
    }

    public function actionAjaxSetDelivery() {
        if (Yii::$app->request->post()) {
            $client = $this->currentUser->organization;
            $order_id = Yii::$app->request->post('order_id');
            $delivery_date = Yii::$app->request->post('delivery_date');
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $oldDateSet = isset($order->requested_delivery);
            if ($order) {
                //$timestamp = \DateTime::createFromFormat('d.m.Y H:i:s', $delivery_date. ' 23:59:59');
                $timestamp = date('Y-m-d H:i:s', strtotime($delivery_date . ' 23:59:59'));

                $order->requested_delivery = $timestamp;
                $order->save();
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($oldDateSet) {
                return $this->successNotify('Дата доставки изменена');
            } else {
                return $this->successNotify('Дата доставки установлена');
            }
        }
    }

    public function actionRefreshCart() {
        $client = $this->currentUser->organization;
        $orders = $client->getCart();
        return $this->renderAjax('_cart', compact('orders'));
    }

    public function actionIndex() {
        $organization = $this->currentUser->organization;
        $searchModel = new OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($organization->getEarliestOrderDate(), "php:d.m.Y");
        ;
        $params = Yii::$app->request->getQueryParams();
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $params['OrderSearch']['client_id'] = $this->currentUser->organization_id;
            $params['OrderSearch']['client_search_id'] = $this->currentUser->organization_id;
            $newCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
            $processingCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
            $fulfilledCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
            $query = Yii::$app->db->createCommand('select sum(total_price) as total from `order` where status=' . Order::STATUS_DONE . ' and client_id=' . $organization->id)->queryOne();
            $totalPrice = $query['total'];
        } else {
            $params['OrderSearch']['vendor_id'] = $this->currentUser->organization_id;
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
        $user->organization->markViewed($id);

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
        $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
        $message = "";

        if (Yii::$app->request->post()) {
            $orderChanged = 0;
            $content = Yii::$app->request->post('OrderContent');
            $discount = Yii::$app->request->post('Order');
            foreach ($content as $position) {
                $product = OrderContent::findOne(['id' => $position['id']]);
                $initialQuantity = $product->initial_quantity;
                $allowedStatuses = [
                    Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                    Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                    Order::STATUS_PROCESSING
                ];
                $quantityChanged = ($position['quantity'] != $product->quantity);
                $priceChanged = isset($position['price']) ? ($position['price'] != $product->price) : false;
                if (in_array($order->status, $allowedStatuses) && ($quantityChanged || $priceChanged)) {
                    $orderChanged = ($orderChanged || $quantityChanged || $priceChanged);
                    if ($quantityChanged) {
                        $ed = isset($product->product->ed) ? ' ' . $product->product->ed : '';
                        if ($position['quantity'] == 0) {
                            $message .= "<br/>удалил $product->product_name из заказа";
                        } else {
                            $message .= "<br/>изменил количество $product->product_name с $product->quantity" . $ed . " на $position[quantity]" . $ed;
                        }
                        $product->quantity = $position['quantity'];
                    }
                    if ($priceChanged) {
                        $message .= "<br/>изменил цену $product->product_name с $product->price руб на $position[price] руб";
                        $product->price = $position['price'];
                    }
                    if ($quantityChanged && ($order->status == Order::STATUS_PROCESSING) && !isset($product->initial_quantity)) {
                        $product->initial_quantity = $initialQuantity;
                    }
                    if ($product->quantity == 0) {
                        $product->delete();
                    } else {
                        $product->save();
                    }
                }
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = Order::STATUS_REJECTED;
                $orderChanged = -1;
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = Order::STATUS_CANCELLED;
                $orderChanged = -1;
            }
            if ($orderChanged < 0) {
                $systemMessage = $initiator . ' отменил заказ!';
                $this->sendSystemMessage($user, $order->id, $systemMessage, true);
                if (isset($order->accepted_by_id)) {
                    $this->sendOrderCanceled($order->createdBy, $order->acceptedBy, $order->id);
                }
            }
            if (isset($discount['discount_type']) && isset($discount['discount'])) {
                $order->discount_type = $discount['discount_type'];
                $order->discount = $order->discount_type ? $discount['discount'] : null;
            }
            if ($orderChanged && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $this->sendSystemMessage($user, $order->id, $order->client->name . ' изменил детали заказа №' . $order->id . ":$message");
                if (isset($order->accepted_by_id)) {
                    $this->sendOrderChange($order->createdBy, $order->acceptedBy, $order->id);
                }
            } elseif ($orderChanged && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->acceptedBy, $order->createdBy, $order->id);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . ' получил заказ!';
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order->createdBy, $order->id);
                }
            }
        }

        $order->calculateTotalPrice();
        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('view', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        } else {
            return $this->render('view', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        }
    }

    public function actionCheckout() {
        $client = $this->currentUser->organization;
        $totalCart = 0;

        if (Yii::$app->request->post('action')) {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
        }

        $orders = $client->getCart();
        foreach ($orders as $order) {
            $totalCart += $order->total_price;
        }

        return $this->render('checkout', compact('orders', 'totalCart'));
    }

    public function actionAjaxOrderGrid($id) {
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

        $order->calculateTotalPrice();
        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        return $this->renderPartial('_view-grid', compact('dataProvider', 'order'));
    }

    public function actionAjaxOrderAction() {
        if (Yii::$app->request->post()) {
            $user_id = $this->currentUser->id;
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            $danger = false;
            switch (Yii::$app->request->post('action')) {
                case 'cancel':
                    $order->status = ($organizationType == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                    $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
                    $systemMessage = $initiator . ' отменил заказ!';
                    $danger = true;
                    if (isset($order->accepted_by_id)) {
                        $this->sendOrderCanceled($order->createdBy, $order->acceptedBy, $order->id);
                    }
                    break;
                case 'confirm':
                    if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT)) {
                        $order->status = Order::STATUS_PROCESSING;
                        $systemMessage = $order->client->name . ' подтвердил заказ!';
                        $this->sendOrderProcessing($order->createdBy, $order->acceptedBy, $order->id);
                    } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR)) {
                        $systemMessage = $order->vendor->name . ' подтвердил заказ!';
                        $order->accepted_by_id = $user_id;
                        $order->status = Order::STATUS_PROCESSING;
                        $this->sendOrderProcessing($order->createdBy, $order->acceptedBy, $order->id);
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                        $systemMessage = $order->client->name . ' получил заказ!';
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order->acceptedBy, $order->id);
                    }
                    break;
            }
            if ($order->save()) {
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                return $this->renderPartial('_order-buttons', compact('order', 'organizationType'));
            }
        }
    }

    public function actionSendMessage() {
        $user = $this->currentUser;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');
            $this->sendChatMessage($user, $order_id, $message);
        }
    }

    public function actionAjaxRefreshButtons() {
        if (Yii::$app->request->post()) {
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            return $this->renderPartial('_order-buttons', compact('order', 'organizationType'));
        }
    }

    public function actionAjaxRefreshVendors() {
        if (Yii::$app->request->post()) {
            $client = $this->currentUser->organization;
            $selectedCategory = Yii::$app->request->post("selectedCategory");
            $vendors = $client->getSuppliers($selectedCategory);
            return \yii\helpers\Html::dropDownList('OrderCatalogSearch[selectedVendor]', null, $vendors, ['id' => 'selectedVendor', "class" => "form-control"]);
        }
    }

    public function actionAjaxRefreshStats($setMessagesRead = 0, $setNotificationsRead = 0) {
        $organization = $this->currentUser->organization;
        $newOrdersCount = $organization->getNewOrdersCount();

        $unreadMessagesHtml = '';
        if ($setMessagesRead) {
            $unreadMessages = [];
            $organization->setMessagesRead();
        } else {
            $unreadMessages = $organization->unreadMessages;
            foreach ($unreadMessages as $message) {
                $unreadMessagesHtml .= $this->renderPartial('/order/_header-message', compact('message'));
            }
        }

        $unreadNotificationsHtml = '';
        if ($setNotificationsRead) {
            $unreadNotifications = [];
            $organization->setNotificationsRead();
        } else {
            $unreadNotifications = $organization->unreadNotifications;
            foreach ($unreadNotifications as $message) {
                $unreadNotificationsHtml .= $this->renderPartial('/order/_header-message', compact('message'));
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'newOrdersCount' => $newOrdersCount,
            'unreadMessagesCount' => count($unreadMessages),
            'unreadNotificationsCount' => count($unreadNotifications),
            'unreadMessages' => $unreadMessagesHtml,
            'unreadNotifications' => $unreadNotificationsHtml,
        ];
    }

    private function sendChatMessage($user, $order_id, $message) {
        $order = Order::findOne(['id' => $order_id]);

        $newMessage = new OrderChat(['scenario' => 'userSent']);
        $newMessage->order_id = $order_id;
        $newMessage->sent_by_id = $user->id;
        $newMessage->message = $message;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        $newMessage->save();
        $name = $user->profile->full_name;

        $body = $this->renderPartial('_chat-message', [
            'id' => $newMessage->id,
            'name' => $name,
            'message' => $newMessage->message,
            'time' => $newMessage->created_at,
            'isSystem' => 0,
            'sender_id' => $user->id,
            'ajax' => 1,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 0,
                    'id' => $newMessage->id,
                    'sender_id' => $user->id,
                    'order_id' => $order_id,
                ])
            ]);
        }
        foreach ($vendorUsers as $vendorUser) {
            $channel = 'user' . $vendorUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 0,
                    'id' => $newMessage->id,
                    'sender_id' => $user->id,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
    }

    private function sendSystemMessage($user, $order_id, $message, $danger = false) {
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
        $body = $this->renderPartial('_chat-message', [
            'name' => '',
            'message' => $newMessage->message,
            'time' => $newMessage->created_at,
            'isSystem' => 1,
            'sender_id' => $user->id,
            'ajax' => 1,
            'danger' => $danger,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
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
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
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

    private function successNotify($title) {
        return [
            'success' => true,
            'growl' => [
                'options' => [
//                            'title' => 'test',
                ],
                'settings' => [
                    'element' => 'body',
                    'type' => $title, //'Заказ успешно оформлен',
                    'allow_dismiss' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'center',
                    ],
                    'delay' => 1500,
                    'animate' => [
                        'enter' => 'animated fadeInDown',
                        'exit' => 'animated fadeOutUp',
                    ],
                    'offset' => 75,
                    'template' => '<div data-notify="container" class="modal-dialog" style="width: 340px;">'
                    . '<div class="modal-content">'
                    . '<div class="modal-header">'
                    . '<h4 class="modal-title">{0}</h4></div>'
                    . '<div class="modal-body form-inline" style="text-align: center; font-size: 36px;"> '
                    . '<span class="glyphicon glyphicon-thumbs-up"></span>'
                    . '</div></div></div>',
                ]
            ]
        ];
    }

    private function sendOrderChange($sender, $recipient, $order_id) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: измененения в заказе №" . $order_id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order_id;
        $dataProvider = $searchModel->search($params);

//        Yii::$app->mailqueue->compose('orderChange', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order_id", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderDone($sender, $recipient, $order_id) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: заказ №" . $order_id . " выполнен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order_id;
        $dataProvider = $searchModel->search($params);

//        Yii::$app->mailqueue->compose('orderDone', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order_id", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderCreated($sender, $recipientOrg, $order_id) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "f-keeper: Создан новый заказ №" . $order_id . "!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order_id;
        $dataProvider = $searchModel->search($params);

        foreach ($recipientOrg->users as $recipient) {
            $email = $recipient->email;
//            Yii::$app->mailqueue->compose('orderCreated', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
            $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order_id", "dataProvider"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
        }
    }

    private function sendOrderProcessing($sender, $recipient, $order_id) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: заказ №" . $order_id . " подтвержден!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order_id;
        $dataProvider = $searchModel->search($params);

//        Yii::$app->mailqueue->compose('orderProcessing', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order_id", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderCanceled($sender, $recipient, $order_id) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: заказ №" . $order_id . " отменен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order_id;
        $dataProvider = $searchModel->search($params);

//        Yii::$app->mailqueue->compose('orderCanceled', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order_id", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function saveCartChanges($content) {
        foreach ($content as $position) {
            $product = OrderContent::findOne(['id' => $position['id']]);
            if ($product->quantity == 0) {
                $product->delete();
            } else {
                $product->save();
            }
        }
    }

}
