<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoWaybill;
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
        $orderID = $post['order_id'];
        $iikoWaybill = iikoWaybill::find()->where(['order_id' => $orderID])->andWhere('status_id > 1')->all();
        $arr = [];
        $i = 0;
        foreach ($iikoWaybill as $item) {
            $arr[$i]['num_code'] = $item->num_code;
            $arr[$i]['agent_denom'] = $item->agent->denom ?? 'Не указано';
            $arr[$i]['store_denom'] = $item->store->denom ?? 'Не указано';
            $arr[$i]['doc_date'] = \Yii::$app->formatter->format($item->doc_date, 'date');
            $arr[$i]['status_denom'] = $item->status->denom;
            $i++;
        }
        return $arr;
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
     * iiko: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function handleWaybill(array $post): array
    {
        $order_id = $post['order_id'];
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (!$ord) {
            throw new BadRequestHttpException('No order with ID ' . $order_id);
        }

        if (isset($post['waybill_id'])){

        }
        $model = new iikoWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;
        $model->agent_uuid = $post['agent_uuid'] ?? '';
        $model->num_code = $post['num_code'] ?? null;
        $model->text_code = $post['text_code'] ?? '';
        $model->store_id = $post['store_id'] ?? null;
        $model->doc_date = $post['doc_date'] ?? '';
        $model->note = $post['note'] ?? '';
        try{
            $model->validate();
        }catch (ValidationException $e){
            throw new BadRequestHttpException($e->getMessage());
        }
        $model->save();

        return [
            'success' => true,
            'waybill_id' => $model->id
        ];
    }
}