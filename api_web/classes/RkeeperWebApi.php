<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\modules\rkeeper\models\rkeeperOrder;
use api_web\modules\integration\modules\rkeeper\models\rkeeperStore;

class RkeeperWebApi extends WebApi
{
    /**
     * rkeeper: Список Накладных к заказу
     * @param array $post
     * @return array
     */
    public function getOrderWaybillsList(array $post): array
    {
        return (new rkeeperOrder())->getOrderWaybillsList($post);
    }

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
     * iiko: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     */
    public function handleWaybill(array $post): array
    {
        return (new rkeeperOrder())->handleWaybill($post);
    }

    /**
     * rkeeper: Справочник складов
     * @param array $post
     * @return array
     */
     public function getStoreList(array $post): array
     {
         return (new rkeeperStore())->getStoreList($post);
     }
}