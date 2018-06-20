<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\modules\iiko\models\iikoOrder;

class IikoWebApi extends WebApi
{
    /**
     * iiko: Список Накладных к заказу
     * @param array $post
     * @return array
     */
    public function getOrderWaybillsList(array $post): array
    {
        return (new iikoOrder())->getOrderWaybillsList($post);
    }

    /**
     * iiko: Завершенные заказы
     * @param array $post
     * @return array
     */
    public function getCompletedOrdersList(array $post): array
    {
        return (new iikoOrder())->getCompletedOrdersList($post);
    }

    /**
     * iiko: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     */
    public function handleWaybill(array $post): array
    {
        return (new iikoOrder())->handleWaybill($post);
    }
}