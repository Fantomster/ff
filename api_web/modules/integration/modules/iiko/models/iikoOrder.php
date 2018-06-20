<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoWaybill;
use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\search\OrderSearch;
use Yii;

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
     * iiko: Создание накладной
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function createWaybill(array $post): array
    {
        if(!isset($post['search']['user_id'])){
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out_six', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $arr = (new OrderSearch())->searchWaybillWebApi($post);
        return $arr;
    }
}