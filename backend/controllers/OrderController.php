<?php

namespace backend\controllers;

use common\models\OperatorCall;
use common\models\OperatorTimeout;
use common\models\OperatorVendorComment;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\search\OrderContentSearch;
use common\models\search\OrderOperatorSearch;
use common\models\User;
use Yii;
use common\models\Order;
use common\models\OrderContent;
use common\models\Role;
use common\models\OrderAttachment;
use backend\models\OrderSearch;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
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
                            'operator',
                            'operator-check-timeout',
                            'operator-change-attribute',
                            'operator-set-to-order'
                        ],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxAddToOrder()
    {
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
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $delay = in_array(Yii::$app->user->id, Yii::$app->params['timeKeepers']) ? 0 : 1;
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $delay);
        //$dataProvider->sort = ['defaultOrder' => ['created_at' => SORT_DESC]];

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $model->id;
        $dataProvider = $searchModel->search($params);

        return $this->render('view', [
            'model'        => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionEdit($id, $attachment_id = null)
    {
        $editableOrders = [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            OrderStatus::STATUS_PROCESSING,
            OrderStatus::STATUS_DONE,
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
            if (isset($order->assignment)) {
                $order->assignment->load(Yii::$app->request->post());
            }
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
                $order->status = OrderStatus::STATUS_REJECTED;
                $orderChanged = -1;
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = OrderStatus::STATUS_CANCELLED;
                $orderChanged = -1;
            }
            $order->calculateTotalPrice();
            $order->save();
            if (isset($order->assignment)) {
                $order->assignment->save();
            }
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

    public function actionAjaxShowProducts($order_id)
    {
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

    public function actionGetAttachment($id)
    {
        $attachment = OrderAttachment::findOne(['id' => $id]);
        $attachment->getFile();
    }

    public function actionWithAttachments()
    {
        $searchModel = new \backend\models\OrderWithAttachmentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('with-attachments', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('with-attachments', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionAssign($id)
    {
        $assignment = \common\models\OrderAssignment::findOne(['order_id' => $id]);
        if (empty($assignment)) {
            $assignment = new \common\models\OrderAssignment(['order_id' => $id, 'assigned_by' => Yii::$app->user->id]);
        }
        $assignment->load(Yii::$app->request->post());
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($assignment->save()) {
            return true;
        } else {
            return ['output' => '', 'message' => ''];
        }
    }

    /**
     * Страница оператора заказов
     *
     * @return string
     */
    public function actionOperator()
    {
        $searchModel = new OrderOperatorSearch();
        $searchModel->user_id = \Yii::$app->user->getId();
        $searchModel->load(Yii::$app->request->queryParams);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $statistic = OperatorCall::find()
            ->select(['count(order_id) as cnt', 'status_call_id as status'])
            ->leftJoin('order', 'order.id = operator_call.order_id')
            ->where("status_call_id != :status and order.created_at > '2018-10-17 00:00:00'", [":status" => OperatorCall::STATUS_COMPLETE])
            ->groupBy(['status_call_id'])
            ->orderBy(['status_call_id' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->render('operator', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'user_id' => $searchModel->user_id, 'statistic' => $statistic]);
    }

    /**
     * Изменение атрибутов звонка
     *
     * @return string
     */
    public function actionOperatorChangeAttribute()
    {
        if (\Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $nameAttribute = Yii::$app->request->post('name');
            $valueAttribute = Yii::$app->request->post('value');

            if($nameAttribute == 'vendor_comment') {
                $model = OperatorVendorComment::findOne(['vendor_id' => $id]) ?? new OperatorVendorComment();
                $model->vendor_id = $id;
                $nameAttribute = 'comment';
            }
            else {
                $model = OperatorCall::findOne($id);
            }

            $model->{$nameAttribute} = $valueAttribute;
            if (!$model->save()) {
                return implode(", ",$model->getFirstErrors());
            }
        }
    }

    /**
     * Установить оператора к заказу
     *
     * @return string
     */
    public function actionOperatorSetToOrder()
    {
        if (\Yii::$app->request->isAjax) {

            $wait = OperatorTimeout::getTimeoutOperator(Yii::$app->user->getId());
            if ($wait > 0) {
                exit("Нужно подождать {$wait} секунд.");
            }

            $id = Yii::$app->request->post('id');
            $model = OperatorCall::findOne($id);
            if (empty($model)) {
                $model = new OperatorCall([
                    'order_id'       => $id,
                    'operator_id'    => Yii::$app->user->getId(),
                    'status_call_id' => OperatorCall::STATUS_OPEN
                ]);

                if (!$model->save()) {
                    exit('ERROR save OrderController->actionOperatorSetToOrder!');
                }

                $countCall = OperatorCall::find()
                    ->where(['operator_id' => Yii::$app->user->getId()])
                    ->andWhere('status_call_id not in (:status_complete,:status_controll)',
                        [':status_complete' => OperatorCall::STATUS_COMPLETE,
                         ':status_controll' => OperatorCall::STATUS_CONTROLL])->count();

                if ($countCall > 1) {
                    $modelTimeout = OperatorTimeout::findOne(['operator_id' => Yii::$app->user->getId()]);
                    if (!isset($modelTimeout)) {
                        $modelTimeout = new OperatorTimeout(['operator_id' => Yii::$app->user->getId()]);
                    }
                    $modelTimeout->timeout_at = \gmdate('Y-m-d H:i:s');
                    $modelTimeout->timeout = $countCall * (10 + $countCall);
                    $modelTimeout->save();
                }
            } else {
                $user = User::findOne($model->operator_id);
                exit('Оператор уже установлен: ' . $user->profile->full_name);
            }
        }
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.order.page_error', ['ru' => 'The requested page does not exist.']));
        }
    }

}
