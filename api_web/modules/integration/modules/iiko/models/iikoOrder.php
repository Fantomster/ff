<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\iikoWaybillData;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\search\OrderSearch;
use Yii;
use yii\web\BadRequestHttpException;

class iikoOrder extends WebApi
{
    /**
     * iiko: Список Накладных к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getOrderWaybillsList(array $post)
    {
        $orderID = (int)$post['order_id'];
        $iikoWaybill = iikoWaybill::find()->where(['order_id' => $orderID])->all();
        $result = [];
        if (!empty($iikoWaybill)) {
            foreach ($iikoWaybill as $item) {
                $result[] = $this->prepareWaybill($item);
            }
        }
        return $result;
    }

    /**
     * iiko: Завершенные заказы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getCompletedOrdersList(array $post): array
    {
        $post['search']['user_id'] = $this->user->id;
        $arr = (new OrderSearch())->searchWaybillWebApi($post);
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

        $model = iikoWaybill::findOne((int)$post['waybill_id']);
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
     * iiko: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function handleWaybill(array $post): array
    {
        $order_id = isset($post['order_id']) ? (int)$post['order_id'] : null;
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (isset($post['waybill_id'])) {
            $model = iikoWaybill::findOne(['id' => $post['waybill_id']]);
        } else {
            $model = new iikoWaybill();
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
        $model->text_code = $post['text_code'] ?? '';
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
     * @param iikoWaybill $item
     * @param bool $data
     * @return array
     */
    private function prepareWaybill(iikoWaybill $item, $data = false)
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
     * @param iikoWaybillData $model
     * @return array
     */
    private function prepareWaybillData(iikoWaybillData $model)
    {
        return [
            'mixcart_product_id' => $model->product_id,
            'mixcart_product_name' => $model->fproductname->product,
            'mixcart_product_ed' => $model->fproductname->ed,
            'iiko_product_id' => $model->product_rid ?? null,
            'iiko_product_name' => $model->product->denom ?? null,
            'iiko_product_ed' => $model->product->unit ?? null,
            'order_position_count' => round($model->waybill->order->getOrderContent()->where(['product_id' => $model->product_id])->one()->quantity, 3),
            'iiko_position_count' => round($model->quant, 3),
            'koef' => $model->koef,
            'sum_without_nds' => $model->sum,
            'sum' => round($model->sum + ($model->sum * $model->vat / 10000), 2),
            'nds' => ceil($model->vat / 100),
        ];
    }
}