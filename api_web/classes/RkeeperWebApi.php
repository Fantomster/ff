<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\modules\rkeeper\models\rkeeperOrder;

class RkeeperWebApi extends WebApi
{
    /**
     * iiko: Список Накладных к заказу
     * @param array $post
     * @return array
     */
   /* public function getOrderWaybillsList(array $post): array
    {
        return (new iikoOrder())->getOrderWaybillsList($post);
    }*/

    /**
     * rkeeper: Завершенные заказы
     * @param array $post
     * @return array
     */
    public function getCompletedOrdersList(array $post): array
    {
        return (new rkeeperOrder())->getCompletedOrdersList($post);
    }

    /**
     * iiko: Создание накладной к заказу
     * @param array $post
     * @return array
     */
   /* public function createWaybill(array $post): array
    {
        return (new iikoOrder())->createWaybill($post);
    }*/
}