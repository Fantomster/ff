<?php

namespace backend\controllers;

use common\models\search\OrderContentSearch;
use Yii;
use common\models\Order;
use common\models\OrderContent;
use common\models\Role;
use common\models\OrderAttachment;
use backend\models\OrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\web\BadRequestHttpException;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'edit',
                            'get-attachment',
                            'with-attachments',
                            'ajax-show-products',
                            'ajax-add-to-order',
                            'assign',
                        ],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxAddToOrder() {
        $post = Yii::$app->request->post();

        if (OrderContent::findOne(['order_id' => $post['order_id'], 'product_id' => $post['product_id']]) != null)
            throw new BadRequestHttpException('This product already exists');

        $product = \common\models\CatalogGoods::findOne(['base_goods_id' => $post['product_id'], 'cat_id' => $post['cat_id']]);

        if ($product) {
            $product_id = $product->baseProduct->id;
            $price = $product->price;
            $product_name = $product->baseProduct->product;
            $vendor = $product->organization;
            $units = $product->baseProduct->units;
            $article = $product->baseProduct->article;
        } else {
            $product = \common\models\CatalogBaseGoods::findOne(['id' => $post['product_id'], 'cat_id' => $post['cat_id']]);
            if ($product == null) {
                throw new BadRequestHttpException('This product not found');
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $units = $product->units;
            $article = $product->article;
        }

        $position = new OrderContent();
        $position->order_id = $post['order_id'];
        $position->product_id = $product_id;
        $position->quantity = $post['quantity'];
        $position->price = $price;
        $position->product_name = $product_name;
        $position->units = $units;
        $position->article = $article;

        $order = $position->order;

        if (!$position->save(false)) {
            throw new BadRequestHttpException('SaveError');
        }

        $order->calculateTotalPrice();
        $order->save();
        return true;
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider->sort = ['defaultOrder' => ['created_at' => SORT_DESC]];

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        $model = $this->findModel($id);
        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $model->id;
        $dataProvider = $searchModel->search($params);

        return $this->render('view', [
                    'model' => $model,
                    'dataProvider' => $dataProvider
        ]);
    }

    public function actionEdit($id, $attachment_id = null) {
        $editableOrders = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];

        $order = Order::findOne(['id' => $id, 'status' => $editableOrders]);

        $currentAttachment = null;

        if (empty($order)) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        if (!empty($attachment_id)) {
            $currentAttachment = OrderAttachment::findOne(['id' => $attachment_id, 'order_id' => $id]);
        }

        $currencySymbol = $order->currency->symbol;

        if (Yii::$app->request->post()) {
            $order->load(Yii::$app->request->post());
            $content = Yii::$app->request->post('OrderContent');
            $discount = Yii::$app->request->post('Order');
            foreach ($content as $position) {
                $product = OrderContent::findOne(['id' => $position['id']]);
                $product->quantity = $position['quantity'];
                $product->price = $position['price'];
                if ($product->quantity == 0) {
                    $product->delete();
                } else {
                    $product->save();
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
            $order->calculateTotalPrice();
            $order->save();
        }


        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('edit', compact('order', 'currentAttachment', 'searchModel', 'dataProvider'));
        } else {
            return $this->render('edit', compact('order', 'currentAttachment', 'searchModel', 'dataProvider'));
        }
    }

    public function actionAjaxShowProducts($order_id) {
        $order = Order::findOne(['id' => $order_id]);

        $params = Yii::$app->request->getQueryParams();

        $productsSearchModel = new \common\models\search\OrderProductsSearch();
        $params['OrderProductsSearch'] = (Yii::$app->request->isPost) ? Yii::$app->request->post("OrderProductsSearch") : Yii::$app->request->get("OrderProductsSearch");
        $productsDataProvider = $productsSearchModel->search($params, $order);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('/order/add-position/_view', compact('productsSearchModel', 'productsDataProvider', 'order'));
        } else {
            return $this->renderAjax('/order/add-position/_view', compact('productsSearchModel', 'productsDataProvider', 'order'));
        }
    }

    public function actionGetAttachment($id) {
        $attachment = OrderAttachment::findOne(['id' => $id]);
        $attachment->getFile();
    }

    public function actionWithAttachments() {
        $searchModel = new \backend\models\OrderWithAttachmentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('with-attachments', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('with-attachments', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionAssign($id) {
        $assignment = \common\models\OrderAssignment::findOne(['order_id' => $id]);
        if (empty($assignment)) {
            $assignment = new \common\models\OrderAssignment(['order_id' => $id, 'assigned_by' => Yii::$app->user->id]);
        }
        $assignment->load(Yii::$app->request->post());
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($assignment->save()) {
            return true;
        } else {
            return ['output'=>'', 'message'=>''];
        }
    }
    
//    /**
//     * Creates a new Order model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new Order();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }
//
//    /**
//     * Updates an existing Order model.
//     * If update is successful, the browser will be redirected to the 'view' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('update', [
//                'model' => $model,
//            ]);
//        }
//    }
//
//    /**
//     * Deletes an existing Order model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.order.page_error', ['ru' => 'The requested page does not exist.']));
        }
    }

}
