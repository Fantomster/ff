<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\modules\one_s\models\one_sAgent;
use api_web\modules\integration\modules\one_s\models\one_sOrder;

class OneSWebApi extends WebApi
{
    /**
     * one_s: Список Накладных к заказу
     *
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getOrderWaybillsList(array $post): array
    {
        return (new one_sOrder())->getOrderWaybillsList($post);
    }

    /**
     * one_s: Завершенные заказы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getCompletedOrdersList(array $post): array
    {
        return (new one_sOrder())->getCompletedOrdersList($post);
    }

    /**
     * one_s: Создание или обновление накладной к заказу
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function handleWaybill(array $post): array
    {
        return (new one_sOrder())->handleWaybill($post);
    }

    /**
     * one_s: Список контрагентов синхронизированных из внешней системы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getAgentsList(array $post): array
    {
        return (new one_sAgent())->getAgentsList($post);
    }

    /**
     * one_s: Обновление данных для связи контрагента
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function updateAgentData(array $post): array
    {
        return (new one_sAgent())->updateAgentData($post);
    }


    /**
     * one_s: Создание сопоставлений номенклатуры накладной с продуктами MixCart
     * @param array $post
     * @return array
     */
    public function handleWaybillData(array $post): array
    {
        return (new one_sOrder())->handleWaybillData($post);
    }
}