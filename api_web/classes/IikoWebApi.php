<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\modules\iiko\models\iikoAgent;
use api_web\modules\integration\modules\iiko\models\iikoOrder;
use api_web\modules\integration\modules\iiko\models\iikoSync;

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


    /**
     * iiko: Список контрагентов синхронизированных из внешней системы
     * @param array $post
     * @return array
     */
    public function getAgentsList(array $post): array
    {
        return (new iikoAgent())->getAgentsList($post);
    }


    /**
     * iiko: Создание сопоставлений номенклатуры накладной с продуктами MixCart
     * @param array $post
     * @return array
     */
    public function handleWaybillData(array $post): array
    {
        return (new iikoSync())->handleWaybillData($post);
    }
}