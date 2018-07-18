<?php

namespace frontend\modules\clientintegr\modules\email\controllers;

use common\models\CatalogBaseGoods;
use common\models\IntegrationInvoice;
use common\models\IntegrationInvoiceContent;
use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use common\models\search\OrderSearch;
use common\models\User;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use common\models\search\IntegrationInvoiceSearch;
use yii;

class InvoiceController extends Controller
{

    public function actionIndex()
    {
        $models = new IntegrationInvoiceSearch();
        $dataProvider = $models->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchMOdel' => $models, 'dataProvider' => $dataProvider]);

    }

    public function actionGetContent()
    {
        $id = \Yii::$app->request->post('id');
        $model = IntegrationInvoice::findOne($id);
        return $this->renderAjax('_content', ['model' => $model]);
    }

    public function actionGetSuppliers()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        /**
         * @var $user User
         */
        $user = \Yii::$app->user->identity;
        return $user->organization->getSuppliers(null, false);
    }

    public function actionGetOrders()
    {
        /**
         * @var $user User
         */
        $user = \Yii::$app->user->identity;
        $params = \Yii::$app->request->getQueryParams();
        $params['OrderSearch']['client_id'] = $user->organization_id;
        $params['OrderSearch']['client_search_id'] = $user->organization_id;
        //dd($params);
        $searchModel = new OrderSearch();
        $searchModel->status_array = [Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 5;

        $vendor_id = $params['OrderSearch']['vendor_id'];
        $invoice_id = $params['invoice_id'];

        return $this->renderAjax('_orders', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'vendor_id' => $vendor_id,
            'invoice_id' => $invoice_id,
        ]);
    }

    /**
     * Создание заказа, и товаров с накладной
     * @return array
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionCreateOrder()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax) {
            throw new \Exception('is not AJAX request');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $params = \Yii::$app->request->post();
            $invoice = IntegrationInvoice::findOne($params['invoice_id']);
            $vendor = Organization::findOne($params['vendor_id']);

            //Если прилетел связный заказ
            if (isset($params['order_id'])) {
                $order_model = Order::findOne($params['order_id']);
                if ($order_model) {
                    $order_model->status = Order::STATUS_CANCELLED;
                    $order_model->invoice_relation = $invoice->id;
                    $order_model->save();
                }
            }

            /**
             * @var $user User
             */
            $user = \Yii::$app->user->identity;

            if (empty($vendor)) {
                throw new Exception('Поставщик не определен.');
            }

            if (empty($invoice)) {
                throw new Exception('Нам не удалось найти эту накладную');
            }

            //Создаем товары для накладных, и получаем их модели
            $content = $invoice->getBaseGoods($vendor);
            if (empty($content)) {
                throw new Exception('Содержимое накладной не может быть пустым.');
            }
            //Создаем заказ
            $order = new Order();
            $order->client_id = $user->organization_id;
            $order->vendor_id = $vendor->id;
            $order->created_by_id = $user->id;
            $order->status = Order::STATUS_DONE;
            $order->total_price = 0;
            $order->currency_id = $vendor->baseCatalog->currency_id;
            if (!$order->save()) {
                throw new Exception('Не вышло создать заказ, что то пошло не так.');
            }
            //Создаем детальную часть заказа
            foreach ($content as $value) {
                $model = new OrderContent();
                $model->order_id = $order->id;
                $model->product_id = $value['id'];
                $model->quantity = $value['quantity'];
                $model->price = $value['price'];
                $model->product_name = $value['product_name'];
                $model->units = $value['units'];
                $model->article = $value['article'];
                if (!$model->save()) {
                    throw new Exception('Кусок заказа не сохранился, давайте поробуем снова.');
                }
            }
            //Пересчитаем заказ
            $order->calculateTotalPrice();
            $invoice->order_id = $order->id;
            $invoice->save();
            $transaction->commit();
            return ['status' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionDownload()
    {
        $user = \Yii::$app->user->identity;
        $organization = $user->organization;
        $id = \Yii::$app->request->get('id');
        $model = IntegrationInvoice::find()->where(['id' => $id, 'organization_id' => $organization->id])->one();
        if ($model) {
            header('Content-Type:' . $model->file_mime_type);
            header('Content-Disposition: attachment; filename="' . $model->id . '.xls"');
            echo base64_decode($model->file_content);
        } else {
            throw new Exception('Not access this invoice.');
        }
    }
}