<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
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
use common\models\ManagerAssociate;
use common\models\OrderChat;
use common\components\AccessRule;
use kartik\mpdf\Pdf;
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
//                'only' => [
//                    'index',
//                    'view',
//                    'edit',
//                    'create',
//                    'checkout',
//                    'send-message',
//                    'refresh-cart',
//                    'ajax-add-to-cart',
//                    'ajax-delete-order',
//                    'ajax-make-order',
//                    'ajax-order-action',
//                    'ajax-change-quantity',
//                    'ajax-refresh-buttons',
//                    'ajax-remove-position',
//                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'edit',
                            'send-message',
                            'ajax-order-action',
                            'ajax-cancel-order',
                            'ajax-refresh-buttons',
                            'ajax-order-grid',
                            'ajax-refresh-stats',
                            'ajax-set-comment',
                            'pdf',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                            'checkout',
                            'repeat',
                            'refresh-cart',
                            'ajax-add-to-cart',
                            'ajax-delete-order',
                            'ajax-make-order',
                            'ajax-change-quantity',
                            'ajax-remove-position',
                            'ajax-show-details',
                            'ajax-refresh-vendors',
                            'ajax-set-note',
                            'ajax-set-delivery',
                            'ajax-show-details',
                            'complete-obsolete',
                            'pjax-cart',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//                    throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
//                }
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
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int) $params['OrderCatalogSearch']['selectedVendor'] : null;
            //$selectedVendor = ($selectedCategory == $params['OrderCatalogSearch']['selectedCategory']) ? $params['OrderCatalogSearch']['selectedVendor'] : '';
            //$selectedCategory = $params['OrderCatalogSearch']['selectedCategory'];
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

    public function actionPjaxCart() {
        if (Yii::$app->request->isPjax) {
            $client = $this->currentUser->organization;
            $orders = $client->getCart();
            return $this->renderPartial('_pjax-cart', compact('orders'));
        } else {
            return $this->redirect('/order/checkout');
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

    public function actionAjaxRemovePosition($vendor_id, $product_id) {

        $client = $this->currentUser->organization;

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

        return true;
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

    public function actionAjaxSetComment($order_id) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
//            $order_id = Yii::$app->request->post('order_id');
            $order = Order::find()->where(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            if ($order) {
                $order->comment = Yii::$app->request->post('comment');
                $order->save();
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => "Комментарий добавлен", "comment" => $order->comment, "type" => "success"]; //$this->successNotify("Комментарий добавлен");
            }
            return false;
        }
    }

    public function actionAjaxCancelOrder($order_id = null) {

        $initiator = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
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
                $order->status = ($initiator->type_id == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                $systemMessage = $initiator->name . ' отменил заказ!';
                $danger = true;
                $order->save();
                if ($initiator->type_id == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, isset($order->accepted_by_id) ? $order->acceptedBy : $order->vendor, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order->createdBy, $order);
                }
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => "Заказ успешно отменен!", "type" => "success"];
            }
            return false;
        }
    }

    public function actionAjaxSetNote($product_id) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $note = GoodsNotes::findOne(['catalog_base_goods_id' => $product_id, 'rest_org_id' => $client->id]);
            if (!$note) {
                $note = new GoodsNotes();
                $note->rest_org_id = $client->id;
                $note->catalog_base_goods_id = $product_id;
            }
            $note->note = Yii::$app->request->post("comment");
            $note->save();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $result = ["title" => "Комментарий к товару добавлен", "comment" => $note->note, "type" => "success"];
            return $result;
        }

        return false;
    }

    public function actionAjaxMakeOrder() {
        $client = $this->currentUser->organization;
        $cartCount = $client->getCartCount();

        if (!$cartCount) {
            return false;
        }

        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
            if (!Yii::$app->request->post('all')) {
                $order_id = Yii::$app->request->post('id');
                $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                if ($order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->created_by_id = $this->currentUser->id;
                    $order->created_at = gmdate("Y-m-d H:i:s");
                    $order->save();
                    $this->sendNewOrder($order->vendor);
                    $this->sendOrderCreated($this->currentUser, $order->vendor, $order);
                }
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
                foreach ($orders as $order) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                    $order->created_by_id = $this->currentUser->id;
                    $order->created_at = gmdate("Y-m-d H:i:s");
                    $order->save();
                    $this->sendNewOrder($order->vendor);
                    $this->sendOrderCreated($this->currentUser, $order->vendor, $order);
                }
            }
            $cartCount = $client->getCartCount();
            $this->sendCartChange($client, $cartCount);
            return true;
        }

        return false;
    }

    public function actionAjaxDeleteOrder($all, $order_id = null) {
        $client = $this->currentUser->organization;

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
        return true;
    }

    public function actionAjaxSetDelivery() {
        if (Yii::$app->request->post()) {
            $client = $this->currentUser->organization;
            $order_id = Yii::$app->request->post('order_id');
            $delivery_date = Yii::$app->request->post('delivery_date');
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $oldDateSet = isset($order->requested_delivery);
            if ($order) {
                $timestamp = date('Y-m-d H:i:s', strtotime($delivery_date . ' 19:00:00'));

                $order->requested_delivery = $timestamp;
                $order->save();
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($oldDateSet) {
                $result = ["title" => "Дата доставки изменена", "type" => "success"];
                return $result;
            } else {
                $result = ["title" => "Дата доставки установлена", "type" => "success"];
                return $result;
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

        $params = Yii::$app->request->getQueryParams();
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $params['OrderSearch']['client_search_id'] = $this->currentUser->organization_id;
            $params['OrderSearch']['client_id'] = $this->currentUser->organization_id;
            $newCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
            $processingCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
            $fulfilledCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
            $query = Yii::$app->db->createCommand('select sum(total_price) as total from `order` where status=' . Order::STATUS_DONE . ' and client_id=' . $organization->id)->queryOne();
            $totalPrice = $query['total'];
        } else {
            $params['OrderSearch']['vendor_search_id'] = $this->currentUser->organization_id;
            $params['OrderSearch']['vendor_id'] = $this->currentUser->organization_id;
            $canManage = Yii::$app->user->can('manage');
            if ($canManage) {
                $newCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
                $processingCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
                $fulfilledCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
                $totalPrice = Order::find()->where(['status' => Order::STATUS_DONE, 'vendor_id' => $organization->id])->sum("total_price");
            } else {
                $params['OrderSearch']['manager_id'] = $this->currentUser->id;
                $orderTable = Order::tableName();
                $maTable = ManagerAssociate::tableName();
                $newCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])
                        ->count();
                $processingCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => Order::STATUS_PROCESSING])
                        ->count();
                $fulfilledCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => Order::STATUS_DONE])
                        ->count();
                $totalPrice = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'status' => Order::STATUS_DONE,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'vendor_id' => $organization->id])
                        ->sum("total_price");
            }
        }
        $dataProvider = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        } else {
            return $this->render('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        }
    }

    public function actionView($id) {
        $user = $this->currentUser;
        $user->organization->markViewed($id);

        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $order = $this->findOrder([Order::tableName() . '.id' => $id], Yii::$app->user->can('manage'));
        } else {
            $order = Order::findOne(['id' => $id]);
            ;
        }

        if (empty($order) || !(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
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
                            $oldQuantity = $product->quantity + 0;
                            $newQuantity = $position["quantity"] + 0;
                            $message .= "<br/>изменил количество $product->product_name с $oldQuantity" . $ed . " на $newQuantity" . $ed;
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
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, isset($order->accepted_by_id) ? $order->acceptedBy : $order->vendor, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order->createdBy, $order);
                }
            }
            if (($discount['discount_type']) && ($discount['discount'])) {
                $discountChanged = (($order->discount_type != $discount['discount_type']) || ($order->discount != $discount['discount']));
                if ($discountChanged) {
                    $order->discount_type = $discount['discount_type'];
                    $order->discount = $order->discount_type ? abs($discount['discount']) : null;
                    if ($order->discount_type == Order::DISCOUNT_FIXED) {
                        $message = $order->discount . " руб";
                    } else {
                        $message = $order->discount . "%";
                    }
                    $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' сделал скидку на заказ №' . $order->id . " в размере:$message");
                }
            } else {
                $order->discount_type = Order::DISCOUNT_NO_DISCOUNT;
                $order->discount = null;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' отменил скидку на заказ №' . $order->id);
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $this->sendSystemMessage($user, $order->id, $order->client->name . ' изменил детали заказа №' . $order->id . ":$message");
                if (isset($order->accepted_by_id)) {
                    $this->sendOrderChange($order->createdBy, $order->acceptedBy, $order);
                } else {
                    $this->sendOrderChangeAll($order->createdBy, $order);
                }
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->acceptedBy, $order->createdBy, $order);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . ' получил заказ!';
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order->createdBy, $order);
                }
            }
        }

        $order->calculateTotalPrice();
        $order->save();
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

    public function actionEdit($id) {
        $user = $this->currentUser;
        $user->organization->markViewed($id);

        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $order = $this->findOrder([Order::tableName() . '.id' => $id], Yii::$app->user->can('manage'));
        } else {
            $order = Order::findOne(['id' => $id]);
            ;
        }

        if (empty($order) || !(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
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
        $orderChanged = 0;

        if (Yii::$app->request->post()) {
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
                            $oldQuantity = $product->quantity + 0;
                            $newQuantity = $position["quantity"] + 0;
                            $message .= "<br/>изменил количество $product->product_name с $oldQuantity" . $ed . " на $newQuantity" . $ed;
                        }
                        $product->quantity = $position['quantity'];
                    }
                    if ($priceChanged) {
                        $message .= "<br/>изменил цену $product->product_name с $product->price руб на $position[price] руб";
                        $product->price = $position['price'];
                        if ($user->organization->type_id == Organization::TYPE_RESTAURANT && !$order->vendor->hasActiveUsers()) {
                            $prodFromCat = $product->getProductFromCatalog();
                            if (!empty($prodFromCat)) {
                                $prodFromCat->price = $product->price;
                                $prodFromCat->baseProduct->price = $product->price;
                                $prodFromCat->save();
                                $prodFromCat->baseProduct->save();
                            }
                        }
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
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, isset($order->accepted_by_id) ? $order->acceptedBy : $order->vendor, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order->createdBy, $order);
                }
            }
            if (($discount['discount_type']) && ($discount['discount'])) {
                $discountChanged = (($order->discount_type != $discount['discount_type']) || ($order->discount != $discount['discount']));
                if ($discountChanged) {
                    $order->discount_type = $discount['discount_type'];
                    $order->discount = $order->discount_type ? abs($discount['discount']) : null;
                    if ($order->discount_type == Order::DISCOUNT_FIXED) {
                        $message = $order->discount . " руб";
                    } else {
                        $message = $order->discount . "%";
                    }
                    $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' сделал скидку на заказ №' . $order->id . " в размере:$message");
                }
            } else {
                if ($order->discount > 0) {
                    $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' отменил скидку на заказ №' . $order->id);
                }
                $order->discount_type = Order::DISCOUNT_NO_DISCOUNT;
                $order->discount = null;
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $this->sendSystemMessage($user, $order->id, $order->client->name . ' изменил детали заказа №' . $order->id . ":$message");
                if (isset($order->accepted_by_id)) {
                    $this->sendOrderChange($order->createdBy, $order->acceptedBy, $order);
                } else {
                    $this->sendOrderChangeAll($order->createdBy, $order);
                }
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->acceptedBy, $order->createdBy, $order);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . ' получил заказ!';
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order->createdBy, $order);
                }
            }
            $order->calculateTotalPrice();
            $order->save();
        
//        if ($orderChanged) {
            return $this->redirect(["order/view", "id" => $order->id]);
  //      }
        }

        
        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('edit', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        } else {
            return $this->render('edit', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        }
    }

    public function actionPdf($id) {
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
        $dataProvider->pagination = false;
        
        //return $this->renderPartial('_bill', compact('dataProvider', 'order'));
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            'content' => $this->renderPartial('_bill', compact('dataProvider', 'order')),
            'options' => [
//                'title' => 'Privacy Policy - Krajee.com',
//                'subject' => 'Generating PDF files via yii2-mpdf extension has never been easy'
            //'showImageErrors' => true,
            ],
            'methods' => [
//                'SetHeader' => ['Generated By: Krajee Pdf Component||Generated On: ' . date("r")],
                'SetFooter' => ['|Page {PAGENO}|'],
            ]
        ]);
        return $pdf->render();
    }

    public function actionCheckout() {
        $client = $this->currentUser->organization;
        $totalCart = 0;

        if (Yii::$app->request->post('action') && Yii::$app->request->post('action') == "save") {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => "Изменения сохранены!", "type" => "success"];
        }

        $orders = $client->getCart();
        foreach ($orders as $order) {
            $order->calculateTotalPrice();
            $totalCart += $order->total_price;
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('checkout', compact('orders', 'totalCart'));
        } else {
            return $this->render('checkout', compact('orders', 'totalCart'));
        }
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
            $edit = false;
            switch (Yii::$app->request->post('action')) {
                case 'cancel':
                    $order->status = ($organizationType == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                    $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
                    $systemMessage = $initiator . ' отменил заказ!';
                    $danger = true;
                    if ($organizationType == Organization::TYPE_RESTAURANT) {
                        $this->sendOrderCanceled($order->client, isset($order->accepted_by_id) ? $order->acceptedBy : $order->vendor, $order);
                    } else {
                        $this->sendOrderCanceled($order->vendor, $order->createdBy, $order);
                    }
                    break;
                case 'confirm':
                    if ($order->isObsolete) {
                        $systemMessage = $order->client->name . ' получил заказ!';
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order->acceptedBy, $order);
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT)) {
                        $order->status = Order::STATUS_PROCESSING;
                        $systemMessage = $order->client->name . ' подтвердил заказ!';
                        $this->sendOrderProcessing($order->createdBy, $order->acceptedBy, $order);
                        $edit = true;
                    } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR)) {
                        $systemMessage = $order->vendor->name . ' подтвердил заказ!';
                        $order->accepted_by_id = $user_id;
                        $order->status = Order::STATUS_PROCESSING;
                        $edit = true;
                        $this->sendOrderProcessing($order->createdBy, $order->acceptedBy, $order);
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                        $systemMessage = $order->client->name . ' получил заказ!';
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order->acceptedBy, $order);
                    }
                    break;
            }
            if ($order->save()) {
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                return $this->renderPartial('_order-buttons', compact('order', 'organizationType', 'edit'));
            }
        }
    }

    public function actionCompleteObsolete($id) {
        $currentOrganization = $this->currentUser->organization;
        if ($currentOrganization->type_id === Organization::TYPE_RESTAURANT) {
            $order = Order::findOne(['id' => $id, 'client_id' => $currentOrganization->id]);
        } else {
            $order = Order::findOne(['id' => $id, 'vendor_id' => $currentOrganization->id]);
        }
        if (!isset($order) || !$order->isObsolete) {
            throw new \yii\web\HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }

        $systemMessage = $order->client->name . ' получил заказ!';
        $order->status = Order::STATUS_DONE;
        $order->actual_delivery = gmdate("Y-m-d H:i:s");
        $this->sendOrderDone($order->createdBy, $order->acceptedBy, $order);
        if ($order->save()) {
            $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, false);
            $this->redirect(['order/view', 'id' => $id]);
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
            $edit = false;
            if (in_array($order->status, [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING])) {
                $edit = true;
            }
            return $this->renderPartial('_order-buttons', compact('order', 'organizationType', 'edit'));
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

    public function actionRepeat($id) {
        $order = Order::findOne(['id' => $id]);

        if ($order->client_id !== $this->currentUser->organization_id) {
            throw new \yii\web\HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }

        $newOrder = new Order([
            'client_id' => $order->client_id,
            'vendor_id' => $order->vendor_id,
            'created_by_id' => $order->created_by_id,
            'status' => Order::STATUS_FORMING,
        ]);
        $newContent = [];
        foreach ($order->orderContent as $position) {
            $attributes = $position->copyIfPossible();
            if ($attributes) {
                $newContent[] = new OrderContent($attributes);
            }
        }
        if ($newContent) {
            $currentOrder = Order::findOne([
                        'client_id' => $order->client_id,
                        'vendor_id' => $order->vendor_id,
                        'created_by_id' => $order->created_by_id,
                        'status' => Order::STATUS_FORMING,
            ]);
            if (!$currentOrder) {
                $currentOrder = $newOrder;
                $currentOrder->save();
            }
            foreach ($newContent as $position) {
                $samePosition = OrderContent::findOne([
                            'order_id' => $currentOrder->id,
                            'product_id' => $position->product_id,
                ]);
                if ($samePosition) {
                    $samePosition->quantity += $position->quantity;
                    $samePosition->save();
                } else {
                    $position->order_id = $currentOrder->id;
                    $position->save();
                }
            }
            $currentOrder->calculateTotalPrice();
        }
        $this->redirect(['order/checkout']);
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

    private function sendOrderChange($sender, $recipient, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "f-keeper: измененения в заказе №" . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $email = $recipient->email;
        $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderChangeAll($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "f-keeper: измененения в заказе №" . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->vendor->users as $recipient) {

//        Yii::$app->mailqueue->compose('orderChange', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
            $email = $recipient->email;
            $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
        }
    }

    private function sendOrderDone($sender, $recipient, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        if (empty($recipient)) {
            return;
        }

        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: заказ №" . $order->id . " выполнен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

//        Yii::$app->mailqueue->compose('orderDone', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderCreated($sender, $recipientOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "f-keeper: новый заказ №" . $order->id . "!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($recipientOrg->users as $recipient) {
            $email = $recipient->email;
//            Yii::$app->mailqueue->compose('orderCreated', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
            $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
            if ($recipient->profile->phone && $recipient->profile->sms_allow) {
                $text = $senderOrg->name . " сформировал для вас новый заказ в системе f-keeper №" . $order->id;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
        }
    }

    private function sendOrderProcessing($sender, $recipient, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $email = $recipient->email;
        $subject = "f-keeper: заказ №" . $order->id . " подтвержден!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

//        Yii::$app->mailqueue->compose('orderProcessing', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order", "dataProvider"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();
    }

    private function sendOrderCanceled($senderOrg, $recipient, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "f-keeper: заказ №" . $order->id . " отменен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

//        Yii::$app->mailqueue->compose('orderCanceled', compact("subject", "senderOrg", "order_id", "dataProvider"))
//                ->setTo($email)
//                ->setSubject($subject)
//                ->queue();
        if ($recipient instanceof Organization) {
            foreach ($recipient->users as $user) {
                $email = $user->email;
                $result = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
        } else {
            $email = $recipient->email;
            $result = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
        }
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

    private function findOrder($condition, $canManage = false) {
        if ($canManage) {
            $order = Order::find()->where($condition)->one();
        } else {
            $maTable = ManagerAssociate::tableName();
            $orderTable = Order::tableName();
            $order = Order::find()
                    ->leftJoin("$maTable", "$maTable.organization_id = $orderTable.client_id")
                    ->where($condition)
                    ->andWhere(["$maTable.manager_id" => $this->currentUser->id])
                    ->one();
        }
        return $order;
    }

}
