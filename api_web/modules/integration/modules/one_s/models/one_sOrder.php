<?php

namespace api_web\modules\integration\modules\one_s\models;

use api\common\models\one_s\one_sWaybill;
use api\common\models\one_s\one_sWaybillData;
use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSWaybillData;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\Order;
use common\models\search\OrderSearch;
use yii\web\BadRequestHttpException;

class one_sOrder extends WebApi
{
    /**
     * one_s: Список Накладных к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getOrderWaybillsList(array $post)
    {
        $orderID = (int)$post['order_id'];
        $one_sWaybill = OneSWaybill::find()->where(['order_id' => $orderID])->all();
        $result = [];
        if (!empty($one_sWaybill)) {
            foreach ($one_sWaybill as $item) {
                $result[] = $this->prepareWaybill($item);
            }
        }
        return $result;
    }

    /**
     * one_s: Завершенные заказы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getCompletedOrdersList(array $post): array
    {
        $post['search']['user_id'] = $this->user->id;
        $arr = (new OrderSearch())->searchWaybillWebApi($post, 'api\common\models\one_s\OneSWaybill');
        return $arr;
    }

    /**
     * Информация о накладной
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getWaybill($post)
    {
        if (!$post['waybill_id']) {
            throw new BadRequestHttpException('Empty waybill_id');
        }

        $model = OneSWaybill::findOne((int)$post['waybill_id']);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found WayBill!');
        }

        $access = Order::findOne(['id' => $model->order_id, 'client_id' => $this->user->organization->id]);
        if (empty($access)) {
            throw new BadRequestHttpException('Not found order!!!');
        }

        return $this->prepareWaybill($model, true);
    }

    /**
     * one_s: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function handleWaybill(array $post): array
    {
        $order_id = isset($post['order_id']) ? (int)$post['order_id'] : null;
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (isset($post['waybill_id'])) {
            $model = OneSWaybill::findOne(['id' => $post['waybill_id']]);
        } else {
            $model = new OneSWaybill();
        }
        if ($order_id) {
            $model->order_id = $order_id;
        }
        $model->status_id = 1;
        if (isset($ord->client_id)) {
            $model->org = $ord->client_id;
        }
        $model->agent_uuid = $post['agent_uuid'] ?? '';
        $model->num_code = $post['num_code'] ?? null;
        $model->store_id = $post['store_id'] ?? null;
        $model->doc_date = $post['doc_date'] ?? '';
        $model->note = $post['note'] ?? '';

        if (!$model->validate() || !$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        return [
            'success' => true,
            'waybill_id' => $model->id
        ];
    }

    /**
     * @param OneSWaybill $item
     * @param bool $data
     * @return array
     */
    private function prepareWaybill(OneSWaybill $item, $data = false)
    {
        $result = [
            'waybill_id' => $item->id,
            'order_id' => $item->order->id,
            'num_code' => $item->num_code,
            'agent_denom' => $item->agent->denom ?? 'Не указано',
            'store_denom' => $item->store->denom ?? 'Не указано',
            'doc_date' => \Yii::$app->formatter->format($item->doc_date, 'date'),
            'status_denom' => $item->status->denom
        ];

        if ($data === true) {
            $result['data'] = [];
            if (!empty($item->waybillData)) {
                foreach ($item->waybillData as $modelData) {
                    $result['data'][] = $this->prepareWaybillData($modelData);
                }
            }
        }

        return $result;
    }

    /**
     * @param OneSWaybillData $model
     * @return array
     */
    private function prepareWaybillData(OneSWaybillData $model)
    {
        return [
            'mixcart_product_id' => $model->product_id,
            'mixcart_product_name' => $model->fproductname->product,
            'mixcart_product_ed' => $model->fproductname->ed,
            'one_s_product_id' => $model->product_rid ?? null,
            'one_s_product_name' => $model->product->denom ?? null,
            'one_s_product_ed' => $model->product->unit ?? null,
            'order_position_count' => round($model->waybill->order->getOrderContent()->where(['product_id' => $model->product_id])->one()->quantity, 3),
            'one_s_position_count' => round($model->quant, 3),
            'koef' => $model->koef,
            'sum_without_nds' => $model->sum,
            'sum' => round($model->sum + ($model->sum * $model->vat / 10000), 2),
            'nds' => ceil($model->vat / 100),
        ];
    }
}