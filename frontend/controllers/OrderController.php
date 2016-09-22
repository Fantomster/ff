<?php

namespace frontend\controllers;

use Yii;
use common\models\search\OrderCatalogSearch;
use common\models\CatalogGoods;
use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use common\models\search\OrderSearch;
use common\models\search\OrderContentSearch;
use yii\helpers\Json;
use common\models\OrderChat;

class OrderController extends DefaultController {
    /*
     *  index
     */

    public function actionCreate() {
        $session = Yii::$app->session;
//        $session->remove('categories');
//        $session->remove('vendors');
//        $session->remove('orders');
        $client = $this->currentUser->organization;

        if (!$session->has('categories')) {
            $categories = $client->getRestaurantCategories();
            for ($i = 0; $i < count($categories); $i++) {
                $categories[$i]['selected'] = 0;
            }
            $session['categories'] = $categories;
        } else {
            $categories = $session['categories'];
        }

        if (!$session->has('vendors')) {
            $vendors = $client->getSuppliers($categories);
            for ($i = 0; $i < count($vendors); $i++) {
                $vendors[$i]['selected'] = 0;
            }
            $session['vendors'] = $vendors;
        } else {
            $vendors = $session['vendors'];
        }

        $searchModel = new OrderCatalogSearch();
        $searchModel->vendors = $vendors;
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $searchModel->search($params);

        if ($session->has('orders')) {
            $orders = $session['orders'];
        } else {
            $orders = [];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('_products', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('create', compact('categories', 'vendors', 'searchModel', 'dataProvider', 'orders'));
        }
    }

    public function actionAjaxCategories() {
        $session = Yii::$app->session;
        $client = $this->currentUser->organization;

        $categories = $session['categories'];
        $post = Yii::$app->request->post();
        foreach ($categories as &$category) {
            if ($category['id'] == $post['id']) {
                $category['selected'] = $post['selected'];
            }
        }
        $vendors = $client->getSuppliers($categories);
        for ($i = 0; $i < count($vendors); $i++) {
            $vendors[$i]['selected'] = 1;
        }
        $session['categories'] = $categories;
        $session['vendors'] = $vendors;

        return $this->renderPartial('_vendors', compact('vendors'));
    }

    public function actionAjaxVendors() {
        $session = Yii::$app->session;
        $client = $this->currentUser->organization;

        $vendors = $session['vendors'];
        $post = Yii::$app->request->post();
        foreach ($vendors as &$vendor) {
            if ($vendor['id'] == $post['id']) {
                $vendor['selected'] = $post['selected'];
            }
        }
        $session['vendors'] = $vendors;
    }

    public function actionAjaxAddToCart() {
        $session = Yii::$app->session;
        if ($session->has('orders')) {
            $orders = $session['orders'];
        } else {
            $orders = [];
        }
        $post = Yii::$app->request->post();
        $product = CatalogGoods::findOne(['id' => $post['id']]);
        $vendor = $product->organization;
        $newOrder = true;
        foreach ($orders as &$order) {
            if ($order['vendor_id'] == $vendor->id) {
                $newOrder = false;
                $newProduct = true;
                foreach ($order['content'] as &$prod) {
                    if ($prod['product_id'] == $product->id) {
                        $newProduct = false;
                        $prod['quantity'] ++;
                    }
                }
                if ($newProduct) {
                    $order['content'][$product->id] = [
                        'product_id' => $product->id,
                        'product_name' => $product->baseProduct->product,
                        'quantity' => 1,
                        'price' => $product->price];
                }
            }
        }
        if ($newOrder) {
            $orders[$vendor->id] = [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'content' => [$product->id => [
                        'product_id' => $product->id,
                        'product_name' => $product->baseProduct->product,
                        'quantity' => 1,
                        'price' => $product->price]]
            ];
        }
        $session['orders'] = $orders;
        return $this->renderAjax('_orders', compact('orders'));
    }

    public function actionAjaxModifyCart() {
        $session = Yii::$app->session;
        $orders = $session['orders'];
        $post = Yii::$app->request->post();
        $newContent = $post['content'];

        if (isset($orders[$post['vendor_id']])) {
            foreach ($orders[$post['vendor_id']]['content'] as &$product) {
                if (isset($newContent[$product['product_id']])) {
                    $product['quantity'] = $newContent[$product['product_id']]['quantity'];
                }
                if ($product['quantity'] == 0) {
                    unset($orders[$post['vendor_id']]['content'][$product['product_id']]);
                }
            }
            $showOrder = $orders[$post['vendor_id']];
        }
        $session['orders'] = $orders;
        return $this->renderAjax('_show-order', compact('showOrder'));
    }

    public function actionAjaxMakeOrder() {
        $session = Yii::$app->session;
        $orders = $session['orders'];
        $post = Yii::$app->request->post();
        $newContent = $post['content'];

        if (isset($orders[$post['vendor_id']])) {
            foreach ($orders[$post['vendor_id']]['content'] as &$product) {
                if (isset($newContent[$product['product_id']])) {
                    $product['quantity'] = $newContent[$product['product_id']]['quantity'];
                }
                if ($product['quantity'] == 0) {
                    unset($orders[$post['vendor_id']]['content'][$product['product_id']]);
                }
            }
        }

        $order = new Order();
        $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
        $order->created_by_id = $this->currentUser->id;
        $order->client_id = $this->currentUser->organization_id;
        $order->vendor_id = $post['vendor_id'];
        $order->save();

        $totalPrice = 0;
        foreach ($orders[$post['vendor_id']]['content'] as $position) {
            $orderContent = new OrderContent();
            $orderContent->order_id = $order->id;
            $orderContent->product_id = $position['product_id'];
            $orderContent->quantity = $position['quantity'];
            $orderContent->price = (int) $position['price']; //временно для теста до фикса соответствующей модели
            $orderContent->save();
            $totalPrice += ($orderContent->price * $orderContent->quantity);
        }

        $order->total_price = $totalPrice;
        $order->save();

        unset($orders[$post['vendor_id']]);
        $session['orders'] = $orders;

        $message = "Заказ создан!";
        return $this->renderAjax('_order-message', compact('message'));
    }

    public function actionAjaxShowOrder($vendor_id) {
        $session = Yii::$app->session;
        if ($session->has('orders')) {
            $orders = $session['orders'];
        } else {
            $orders = [];
        }
        $showOrder = [];
        foreach ($orders as $order) {
            if ($order['vendor_id'] == $vendor_id) {
                $showOrder = $order;
            }
        }
        return $this->renderAjax('_show-order', compact('showOrder'));
    }

    public function actionAjaxClearOrder() {
        $session = Yii::$app->session;
        $orders = $session['orders'];
        $post = Yii::$app->request->post();

        if (isset($orders[$post['vendor_id']])) {
            unset($orders[$post['vendor_id']]);
        }
        $session['orders'] = $orders;
        $message = "Заказ отменен!";
        return $this->renderAjax('_order-message', compact('message'));
    }

    public function actionAjaxOrderRefresh() {
        $session = Yii::$app->session;
        if ($session->has('orders')) {
            $orders = $session['orders'];
        } else {
            $orders = [];
        }
        return $this->renderAjax('_orders', compact('orders'));
    }

    public function actionIndex() {
        $searchModel = new OrderSearch();
        $params = Yii::$app->request->getQueryParams();
        $organization = $this->currentUser->organization;
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $params['OrderSearch']['client_id'] = $this->currentUser->organization_id;
        } else {
            $params['OrderSearch']['vendor_id'] = $this->currentUser->organization_id;
        }
        $dataProvider = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('index', compact('searchModel', 'dataProvider'));
        }
    }

    public function actionView($id) {
        $order = Order::findOne(['id' => $id]);
        $user = $this->currentUser;
        $organizationType = $user->organization->type_id;
        if (isset($_POST['hasEditable'])) {
            $model = OrderContent::findOne(['id' => Yii::$app->request->post('editableKey')]);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $posted = current($_POST['OrderContent']);
            $post = ['OrderContent' => $posted];
            if ($model->load($post) && in_array($order->status, [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR])) {
                $value = (isset($posted['quantity'])) ? $model->quantity : $model->price;
                $model->save();
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                } else {
                    $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                }
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

    public function actionAjaxOrderAction($id, $action) {
        $organizationType = $this->currentUser->organization->type_id;
        return $this->renderPartial('_order-buttons', compact('order', 'organizationType'));
    }
    
    public function actionSendMessage() {
        $user = $this->currentUser;
        if (Yii::$app->request->post()) {
            $name = $user->profile->full_name;
            $message = Yii::$app->request->post('message');
            $channel = 'order' . Yii::$app->request->post('order_id');
            $newMessage = new OrderChat();
            $newMessage->order_id = Yii::$app->request->post('order_id');
            $newMessage->sent_by_id = $user->id;
            $newMessage->message = $message;
            $newMessage->save();
            
            return Yii::$app->redis->executeCommand('PUBLISH', [
                        'channel' => 'chat',
                        'message' => Json::encode(['name' => $name, 'message' => $newMessage->message, 'channel' => $channel, 'isSystem' => 0])
            ]);
        }
    }
}
