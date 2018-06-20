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
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function getOrderWaybillsList(array $post): array
    {
        return (new iikoOrder())->getOrderWaybillsList($post);
    }


    /**
     * iiko: Завершенные заказы
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function getCompletedOrdersList(array $post): array
    {
        return (new iikoOrder())->getCompletedOrdersList($post);
    }


    /**
     * iiko: Создание накладной
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function createWaybill(array $post): array
    {
        return (new iikoOrder())->createWaybill($post);
    }
}