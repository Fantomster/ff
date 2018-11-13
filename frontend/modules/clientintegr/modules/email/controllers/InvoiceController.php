<?php

namespace frontend\modules\clientintegr\modules\email\controllers;

use common\models\CatalogBaseGoods;
use common\models\IntegrationInvoice;
use common\models\IntegrationInvoiceContent;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\search\OrderSearch;
use common\models\User;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use common\models\search\IntegrationInvoiceSearch;
use yii;
use yii\helpers\Url;

class InvoiceController extends Controller
{

    public function actionIndex()
    {
        Url::remember();
        $searchModel = new IntegrationInvoiceSearch();

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);

        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asDate($this->getEarliestInvoice($organization->id), "php:d.m.Y");

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageParam = 'page_outer';
        $vi = 'index';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
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

        $searchModel = new OrderSearch();
        $searchModel->status_array = [OrderStatus::STATUS_DONE, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 5;

        $vendor_id = $params['OrderSearch']['vendor_id'];
        $invoice_id = $params['invoice_id'];
        $showAll = (isset($params['show_waybill']) && $params['show_waybill'] == 'true') ? 1 : 0;

        $searchModel = new OrderSearch();
        $searchModel->status_array = [OrderStatus::STATUS_DONE, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 5;

        $dataProvider->pagination->pageParam = 'page_order';
        return $this->renderAjax('_orders', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'vendor_id'    => $params['OrderSearch']['vendor_id'],
            'invoice_id'   => $params['invoice_id'],
            'show_waybill' => $showAll
        ]);
    }

    /**
     * Создание заказа, и товаров с накладной
     *
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
                    $order_model->status = OrderStatus::STATUS_CANCELLED;
                    $order_model->invoice_relation = $invoice->id;
                    $order_model->save();
                }
            }

            $user = Yii::$app->user->identity;
            $licenses = $user->organization->getLicenseList();
            $timestamp_now = time();
            if (isset($licenses['rkws'])) {
                $sub0 = explode(' ', $licenses['rkws']->td);
                $sub1 = explode('-', $sub0[0]);
                $licenses['rkws']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
                if ($licenses['rkws']->status_id == 0) {
                    $rk_us = 0;
                }
                if (($licenses['rkws']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['rkws']->td)))) {
                    $link = 'rkws';
                }
            }
            if (isset($licenses['iiko'])) {
                $sub0 = explode(' ', $licenses['iiko']->td);
                $sub1 = explode('-', $sub0[0]);
                $licenses['iiko']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
                if ($licenses['iiko']->status_id == 0) {
                    $lic_iiko = 0;
                }
                if (($licenses['iiko']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['iiko']->td)))) {
                    $link = 'iiko';
                }
            }

            /**
             * @var $user User
             */
            $user = \Yii::$app->user->identity;

            if (empty($vendor)) {
                throw new Exception('Поставщик не определён.');
            }

            if (empty($invoice)) {
                throw new Exception('Нам не удалось найти эту накладную.');
            }

            //Создаём товары для накладных, и получаем их модели
            $content = $invoice->getBaseGoods($vendor);
            $temp = $invoice->addProductsFromTorg12InCatalogGoods($vendor);
            if (empty($content)) {
                throw new Exception('Содержимое накладной не может быть пустым.');
            }
            //Создаём заказ
            $order = new Order();
            $order->client_id = $user->organization_id;
            $order->vendor_id = $vendor->id;
            $order->created_by_id = $user->id;
            $order->status = OrderStatus::STATUS_DONE;
            $order->total_price = 0;
            $order->currency_id = $vendor->baseCatalog->currency_id;
            $order->invoice_relation = $params['invoice_id'];
            if (!$order->save()) {
                throw new Exception('Не вышло создать заказ, что-то пошло не так.');
            }
            //Создаём детальную часть заказа
            foreach ($content as $value) {
                $model = new OrderContent();
                $model->order_id = $order->id;
                $model->product_id = $value['id'];
                $model->quantity = $value['quantity'];
                $model->price = $value['price'];
                $model->product_name = $value['product_name'];
                $model->units = $value['units'];
                $model->article = $value['article'];
                $model->invoice_content_id = $value['invoice_content_id'];
                if (!$model->save()) {
                    throw new Exception('Часть заказа не сохранилась, давайте попробуем снова.');
                }
            }
            //Пересчитаем заказ
            $order->calculateTotalPrice(true, $invoice->total_sum_withouttax);
            $invoice->order_id = $order->id;
            $invoice->save();
            $transaction->commit();
            $page = \common\models\IntegrationInvoice::pageOrder($order->id);
            return ['status' => true, 'order_id' => $order->id, 'us' => $link, 'page' => $page];
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
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

    public function actionListPostav()
    {
        $org_id = $_POST["org_id"];
        $stroka = $_POST["stroka"];
        $res = Organization::getSuppliersByString($org_id, $stroka);
        $res = json_encode($res);
        return $res;
    }

    protected function getEarliestInvoice($org_id)
    {

        $eDate = IntegrationInvoice::find()->andWhere(['organization_id' => $org_id])->orderBy('date ASC')->one();

        return isset($eDate) ? $eDate->date : null;
    }

    public function actionSetVendor()
    {
        $vendor_id = \Yii::$app->request->post('vendor_id');
        $invoice_id = \Yii::$app->request->post('invoice_id');
        $model = IntegrationInvoice::find()->where(['id' => $invoice_id])->one();
        $model->vendor_id = $vendor_id;
        $model->save();
        //$res = json_encode($res);
        return true;
    }

    public function actionGetOrdersTorg12()
    {
        /**
         * @var $user User
         */
        $user = \Yii::$app->user->identity;
        $params = \Yii::$app->request->getQueryParams();
        $params['OrderSearch']['client_id'] = $user->organization_id;
        $params['OrderSearch']['client_search_id'] = $user->organization_id;

        $searchModel = new OrderSearch();
        $searchModel->status_array = [OrderStatus::STATUS_DONE, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
        $dataProvider = $searchModel->searchForTorg12($params);
        $dataProvider->pagination->pageSize = 5;

        if (\Yii::$app->request->get('show_waybill')) {
            if (\Yii::$app->request->get('show_waybill') == 'true') {
                $showAll = 1;
            } else {
                $showAll = 0;
            }
        } else {
            $showAll = 0;
        }
        $params['show_waybill'] = $showAll;

        $searchModel = new OrderSearch();
        $searchModel->status_array = [OrderStatus::STATUS_DONE, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
        $dataProvider = $searchModel->searchForTorg12($params);
        $dataProvider->pagination->pageSize = 5;

        $dataProvider->pagination->pageParam = 'page_order';
        return $this->renderAjax('_orders', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'vendor_id'    => $params['OrderSearch']['vendor_id'],
            'invoice_id'   => $params['invoice_id'],
            'show_waybill' => $params['show_waybill']
        ]);
    }

}
