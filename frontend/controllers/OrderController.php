<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
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
use common\models\guides\Guide;
use common\models\search\GuideSearch;
use common\models\guides\GuideProduct;
use common\models\search\GuideProductsSearch;
use common\models\search\BaseProductSearch;
use common\models\search\VendorSearch;
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
                            'export-to-xls'
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
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                            'guides',
                            'favorites',
                            'edit-guide',
                            'reset-guide',
                            'save-guide',
                            'checkout',
                            'repeat',
                            'refresh-cart',
                            'ajax-add-to-cart',
                            'ajax-add-guide-to-cart',
                            'ajax-delete-order',
                            'ajax-make-order',
                            'ajax-change-quantity',
                            'ajax-remove-position',
                            'ajax-show-details',
                            'ajax-refresh-vendors',
                            'ajax-set-note',
                            'ajax-set-delivery',
                            'ajax-show-details',
                            'ajax-create-guide',
                            'ajax-add-to-guide',
                            'ajax-delete-guide',
                            'ajax-remove-from-guide',
                            'ajax-show-guide',
                            'ajax-select-vendor',
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
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//                    throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
//                }
            ],
        ];
    }

    public function actionExportToXls() {
        $selected = Yii::$app->request->get('selected');
        if (!empty($selected)) {
            $model = \Yii::$app->db->createCommand("
                select 
                    cbg.article,
                    cbg.product as product, 
                    sum(quantity) as total_quantity,
                    cbg.ed
                from `order_content` oc 
                left join `catalog_base_goods` cbg on oc.`product_id` = cbg.`id`
                where oc.order_id in ($selected)
                group by cbg.id")->queryAll();

            $objPHPExcel = new \PHPExcel();
            $sheet = 0;
            $objPHPExcel->setActiveSheetIndex($sheet);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('отчет')
                    ->setCellValue('A1', 'Артикул')
                    ->setCellValue('B1', 'Наименование товара')
                    ->setCellValue('C1', 'Кол-во')
                    ->setCellValue('D1', 'Ед.изм');
            $row = 2;
            foreach ($model as $foo) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $foo['article']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, Html::decode(Html::decode(Html::decode($foo['product']))));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $foo['total_quantity']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $foo['ed']);
                $row++;
            }
            header('Content-Type: application/vnd.ms-excel');
            $filename = "otchet_" . date("d-m-Y-His") . ".xls";
            header('Content-Disposition: attachment;filename=' . $filename . ' ');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }

    public function actionCreate() {
        $session = Yii::$app->session;
        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearch();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderCatalogSearch")) {
            $params['OrderCatalogSearch'] = Yii::$app->request->post("OrderCatalogSearch");
            $session['orderCatalogSearch'] = Yii::$app->request->post("OrderCatalogSearch");
        }

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int) $params['OrderCatalogSearch']['selectedVendor'] : null;
        }

        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        if (Yii::$app->request->post("OrderCatalogSearch")) {
        }
        $params['OrderCatalogSearch'] = $session['orderCatalogSearch'];
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

    public function actionGuides() {
        $client = $this->currentUser->organization;
        $searchModel = new GuideSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['GuideSearch'] = Yii::$app->request->post("GuideSearch");

        $dataProvider = $searchModel->search($params, $client->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('guides', compact('dataProvider', 'searchModel'));
        } else {
            return $this->render('guides', compact('dataProvider', 'searchModel'));
        }
    }

    public function actionAjaxDeleteGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        if (isset($guide)) {
            $guide->delete();
            return true;
        }
        return false;
    }

    public function actionAjaxCreateGuide($name) {
        $client = $this->currentUser->organization;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($name && $client->type_id === Organization::TYPE_RESTAURANT) {
            $guide = new Guide();
            $guide->client_id = $client->id;
            $guide->name = $name;
            $guide->type = Guide::TYPE_GUIDE;
            $guide->save();
            return ['type' => 'success', 'url' => \yii\helpers\Url::to(['order/edit-guide', 'id' => $guide->id])];
        } else {
            return ['type' => 'fail'];
        }
    }

    public function actionEditGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        if (empty($guide)) {
            return $this->redirect(['order/guides']);
        }

        $session = Yii::$app->session;

        if (isset($session['currentGuide']) && $id != $session['currentGuide']) {
            unset($session['guideProductList']);
            unset($session['selectedVendor']);
        }
        $session['currentGuide'] = $id;

        $guideProductList = isset($session['guideProductList']) ? $session['guideProductList'] : $guide->guideProductsIds;
        $session['guideProductList'] = $guideProductList;

        $test = $session['selectedVendor'];
        $test2 = $session['guideProductList'];

        $params = Yii::$app->request->getQueryParams();

        $vendorSearchModel = new VendorSearch();
        if (Yii::$app->request->post("VendorSearch")) {
            $session['vendorSearchString'] = Yii::$app->request->post("VendorSearch");
        }
        $params['VendorSearch'] = $session['vendorSearchString'];
        $vendorDataProvider = $vendorSearchModel->search($params, $client->id);
        $vendorDataProvider->pagination = ['pageSize' => 8];

        $productSearchModel = new OrderCatalogSearch();
        $vendors = $client->getSuppliers(null);
        $selectedVendor = $session['selectedVendor'];
        if (empty($selectedVendor)) {
            $selectedVendor = isset(array_keys($vendors)[1]) ? array_keys($vendors)[1] : null;
        }
        //isset($session['selectedVendor']) ? $session['selectedVendor'] : isset(array_keys($vendors)[1]) ? array_keys($vendors)[1] : null;
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, null) : "(0)";
        $productSearchModel->client = $client;
        $productSearchModel->catalogs = $catalogs;
        if (Yii::$app->request->post("OrderCatalogSearch")) {
            $session['orderCatalogSearchString'] = Yii::$app->request->post("OrderCatalogSearch");
        }
        $params['OrderCatalogSearch'] = $session['orderCatalogSearchString'];
        $productDataProvider = $productSearchModel->search($params);
        $productDataProvider->pagination = ['pageSize' => 8];

        $guideSearchModel = new BaseProductSearch();
        if (Yii::$app->request->post("BaseProductSearch")) {
            $session['baseProductSearchString'] = Yii::$app->request->post("BaseProductSearch");
        }
        $params['BaseProductSearch'] = $session['baseProductSearchString'];
        $guideDataProvider = $guideSearchModel->search($params, $guideProductList);
        $guideDataProvider->pagination = ['pageSize' => 7];

        $pjax = Yii::$app->request->get("_pjax");
        if (Yii::$app->request->isPjax && $pjax == '#vendorList') {
            return $this->renderPartial('guides/_vendor-list', compact('vendorDataProvider', 'selectedVendor'));
        } elseif (Yii::$app->request->isPjax && $pjax == '#productList') {
            return $this->renderPartial('guides/_product-list', compact('productDataProvider', 'guideProductList'));
        } elseif (Yii::$app->request->isPjax && $pjax == '#guideProductList') {
            return $this->renderPartial('guides/_guide-product-list', compact('guideDataProvider', 'guideProductList'));
        } else {
            return $this->render('guides/edit-guide', compact('guide', 'selectedVendor', 'guideProductList', 'guideProductList', 'vendorSearchModel', 'vendorDataProvider', 'productSearchModel', 'productDataProvider', 'guideSearchModel', 'guideDataProvider'));
        }
    }

    public function actionSaveGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        $session = Yii::$app->session;

        if (isset($session['currentGuide']) && $id != $session['currentGuide']) {
            return $this->redirect(['order/guides']);
        }

        $guideProductList = $session['guideProductList'];

        foreach ($guide->guideProducts as $guideProduct) {
            if (!in_array($guideProduct->cbg_id, $guideProductList)) {
                $guideProduct->delete();
            } else {
                $position = array_search($guideProduct->cbg_id, $guideProductList);
                if ($position !== FALSE) {
                    unset($guideProductList[$position]);
                }
            }
        }

        foreach ($guideProductList as $newProductId) {
            $newProduct = new GuideProduct();
            $newProduct->guide_id = $id;
            $newProduct->cbg_id = $newProductId;
            $newProduct->save();
        }

        unset($session['guideProductList']);
        unset($session['selectedVendor']);
        unset($session['currentGuide']);

        return $this->redirect(['order/guides']);
    }

    public function actionResetGuide() {
        $session = Yii::$app->session;
        unset($session['guideProductList']);
        unset($session['selectedVendor']);
        unset($session['currentGuide']);
        return $this->redirect(['order/guides']);
    }

    public function actionAjaxShowGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        $params = Yii::$app->request->getQueryParams();

        $guideSearchModel = new GuideProductsSearch();
        $params['GuideProductsSearch'] = Yii::$app->request->post("GuideProductsSearch");
        $guideDataProvider = $guideSearchModel->search($params, $guide->id);
        $guideDataProvider->pagination = false;//['pageSize' => 8];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide'));
        } else {
            return $this->renderAjax('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide'));
        }
    }

    public function actionAjaxSelectVendor($id) {
        $session = Yii::$app->session;
        $session['selectedVendor'] = $id;
        return true;
    }

    public function actionAjaxAddToGuide($id) {
        $client = $this->currentUser->organization;
        $session = Yii::$app->session;

        $product = $client->getProductIfAvailable($id);

        if ($product) {
            $guideProductList = isset($session['guideProductList']) ? $session['guideProductList'] : [];
            if (!in_array($product->id, $guideProductList)) {
                $guideProductList[] = $product->id;
                $session['guideProductList'] = $guideProductList;
            }
        }

        return isset($product);
    }

    public function actionAjaxRemoveFromGuide($id) {
        $client = $this->currentUser->organization;
        $session = Yii::$app->session;
        $guideProductList = $session['guideProductList'];
        $positionInGuide = array_search($id, $guideProductList);
        if ($positionInGuide !== FALSE) {
            unset($guideProductList[$positionInGuide]);
            $session['guideProductList'] = $guideProductList;
            return true;
        }
        return false;
    }

    public function actionAjaxAddGuideToCart($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        $guideProducts = Yii::$app->request->post("GuideProduct");
        
        foreach ($guideProducts as $productId => $quantity) {

            if ($quantity <= 0) {
                continue;
            }
            
            $guideProduct = GuideProduct::findOne(['id' => $productId, 'guide_id' => $id]);

            $orders = $client->getCart();

            $product_id = $guideProduct->cbg_id;
            $price = $guideProduct->price;
            $product_name = $guideProduct->baseProduct->product;
            $vendor = $guideProduct->baseProduct->vendor;
            $units = $guideProduct->baseProduct->units;
            $article = $guideProduct->baseProduct->article;
            //$quantity = $guideProduct->baseProduct->units ? $guideProduct->baseProduct->units : 1;
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
            $alteringOrder->calculateTotalPrice();
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return true; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionFavorites() {
        $client = $this->currentUser->organization;

        $params = Yii::$app->request->getQueryParams();
        $params['FavoriteSearch'] = Yii::$app->request->post("FavoriteSearch");

        $searchModel = new \common\models\search\FavoriteSearch();
        $dataProvider = $searchModel->search($params, $client->id);
        $dataProvider->pagination = ['pageSize' => 10];

        return $this->render('favorites', compact('searchModel', 'dataProvider', 'client'));
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
        $post = Yii::$app->request->post();
        $quantity = $post['quantity'];
        if ($quantity <= 0) {
            return false;
        }
        $client = $this->currentUser->organization;
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
                return true; //$this->renderAjax('_orders', compact('orders'));
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
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
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
                $orders[] = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            }
            foreach ($orders as $order) {
                $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $order->created_by_id = $this->currentUser->id;
                $order->created_at = gmdate("Y-m-d H:i:s");
                $order->save();
                $this->sendNewOrder($order->vendor);
                $this->sendOrderCreated($this->currentUser, $order);
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
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
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
                $this->sendOrderChange($order->client, $order);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . ' получил заказ!';
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order);
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
                            $message .= "<br/> удалил $product->product_name из заказа";
                        } else {
                            $oldQuantity = $product->quantity + 0;
                            $newQuantity = $position["quantity"] + 0;
                            $message .= "<br/> изменил количество $product->product_name с $oldQuantity" . $ed . " на $newQuantity" . $ed;
                        }
                        $product->quantity = $position['quantity'];
                    }
                    if ($priceChanged) {
                        $message .= "<br/> изменил цену $product->product_name с $product->price руб на $position[price] руб";
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
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
            }
            if (($discount['discount_type']) && ($discount['discount'])) {
                $discountChanged = (($order->discount_type != $discount['discount_type']) || ($order->discount != $discount['discount']));
                if ($discountChanged) {
                    $order->discount_type = $discount['discount_type'];
                    $order->discount = $order->discount_type ? abs($discount['discount']) : null;
                    $order->calculateTotalPrice();
                    if ($order->discount_type == Order::DISCOUNT_FIXED) {
                        $discountValue = $order->discount . " руб";
                    } else {
                        $discountValue = $order->discount . "%";
                    }
                    $message .= "<br/> сделал скидку на заказ №$order->id в размере: $discountValue";
                    $orderChanged = 1;
                }
            } else {
                if ($order->discount > 0) {
                    $message .= "<br/> отменил скидку на заказ №$order->id";
                    $orderChanged = 1;
                }
                $order->discount_type = Order::DISCOUNT_NO_DISCOUNT;
                $order->discount = null;
                $order->calculateTotalPrice();
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $this->sendSystemMessage($user, $order->id, $order->client->name . ' изменил детали заказа №' . $order->id . ":$message");
//                $subject = $order->client->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
//                foreach ($order->recipientsList as $recipient) {
//                    if (($recipient->organization_id == $order->vendor_id) && $recipient->profile->phone && $recipient->smsNotification->order_changed) {
//                        $text = $subject;
//                        $target = $recipient->profile->phone;
//                        $sms = new \common\components\QTSMS();
//                        $sms->post_message($text, $target);
//                    }
//                }
                $order->calculateTotalPrice();
                $order->save();
                $this->sendOrderChange($order->client, $order);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $order->calculateTotalPrice();
                $order->save();
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order);
//                $subject = $order->vendor->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
//                foreach ($order->client->users as $recipient) {
//                    if ($recipient->profile->phone && $recipient->smsNotification->order_changed) {
//                        $text = $subject;
//                        $target = $recipient->profile->phone;
//                        $sms = new \common\components\QTSMS();
//                        $sms->post_message($text, $target);
//                    }
//                }
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . ' получил заказ!';
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order);
                }
            }
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
//            'showImageErrors' => true,
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
            $systemMessage = '';
            switch (Yii::$app->request->post('action')) {
                case 'cancel':
                    $order->status = ($organizationType == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                    $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
                    $systemMessage = $initiator . ' отменил заказ!';
                    $danger = true;
                    if ($organizationType == Organization::TYPE_RESTAURANT) {
                        $this->sendOrderCanceled($order->client, $order);
                    } else {
                        $this->sendOrderCanceled($order->vendor, $order);
                    }
                    break;
                case 'confirm':
                    if ($order->isObsolete) {
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
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                        $systemMessage = $order->client->name . ' получил заказ!';
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order);
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
        $this->sendOrderDone($order->createdBy, $order);
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
            $canRepeatOrder = false;
            if ($organizationType == Organization::TYPE_RESTAURANT) {
                switch ($order->status) {
                    case Order::STATUS_DONE:
                    case Order::STATUS_REJECTED:
                    case Order::STATUS_CANCELLED:
                        $canRepeatOrder = true;
                        break;
                }
            }
            return $this->renderPartial('_order-buttons', compact('order', 'organizationType', 'edit', 'canRepeatOrder'));
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

    /**
     * Sends email informing both sides about order change details
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderChange($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "Измененения в заказе №" . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_changed) {
                $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_changed) {
                $test = Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));
                $text = $senderOrg->name . " изменил заказ ".Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));//$subject;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
        }
    }

    /**
     * Sends mail informing both sides that order is delivered and accepted
     * 
     * @param User $sender
     * @param Order $order
     */
    private function sendOrderDone($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = "Заказ №" . $order->id . " выполнен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_done) {
                $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_done) {
                $text = $order->vendor->name . " выполнил заказ ".Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));//$order->vendor->name . " выполнил заказ в системе №" . $order->id;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
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

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $test = $order->recipientsList;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_created) {
                $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_created) {
                $text = "Новый заказ от ".$senderOrg->name . ' '.Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));//$order->client->name . " сформировал для Вас заказ в системе №" . $order->id;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
        }
    }

    /**
     * Sends mail informing both sides that vendor confirmed order
     * 
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderProcessing($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "Заказ №" . $order->id . " подтвержден!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_processing) {
                $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_processing) {
                $text = "Заказ у ".$order->vendor->name." согласован ".Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));//"Заказ в системе №" . $order->id . " согласован.";
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
        }
    }

    /**
     * Sends mail informing both sides about cancellation of order
     * 
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderCanceled($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "Заказ №" . $order->id . " отменен!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->emailNotification->order_canceled) {
                $notification = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            if ($recipient->profile->phone && $recipient->smsNotification->order_canceled) {
                $text = $senderOrg->name . " отменил заказ ".Yii::$app->google->shortUrl($order->getUrlForUser($recipient->id));//$senderOrg->name . " отменил заказ в системе №" . $order->id;
                $target = $recipient->profile->phone;
                $sms = new \common\components\QTSMS();
                $sms->post_message($text, $target);
            }
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
