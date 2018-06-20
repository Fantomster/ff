<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\iiko\models\iikoOrder;
use api_web\modules\integration\modules\iiko\models\iikoService;

class OrderController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/iiko/order/list",
     *     tags={"Integration/iiko/order"},
     *     summary="Список завершенных заказов",
     *     description="Список завершенных заказов",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "search": {
     *                                  "order_id": 1,
     *                                  "num_code": 7777,
     *                                  "store_denom": "Не указано",
     *                                  "vendor_id": 555,
     *                                  "actual_delivery": "2018-06-23 09:00:00"
     *                              },
     *                  "pagination":{
     *                              "page": 1,
     *                              "page_size": 12
     *                          }
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       "orders": {
     *                          "order_id": 33251,
     *                          "vendor": "Имя поставщика",
     *                          "delivery_date":"03 Апр 2018",
     *                          "position_count": 8,
     *                          "total_price": 35012.52,
     *                          "currency_id":1,
     *                          "currency": "RUB",
     *                          "status":1,
     *                          "status_text":"Выгружено"
     *                      },
     *                      "pagination": {
     *                                      "page": 1,
     *                                      "total_page": 17,
     *                                      "page_size": 12
     *                                  }
     *              }
     *          )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionList()
    {
        $this->response = $this->container->get('IikoWebApi')->getCompletedOrdersList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/iiko/order/waybills-list",
     *     tags={"Integration/iiko/order"},
     *     summary="Список Накладных к заказу",
     *     description="Список Накладных к заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "order_id": 1
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "num_code": 2222,
     *                "agent_denom": "Не указано",
     *                "store_denom": "Не указано",
     *                "doc_date": "23 июня 2018 г.",
     *                "status_denom": "Выгружена"
     *              }
     *          )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionWaybillsList()
    {
        $this->response = $this->container->get('IikoWebApi')->getOrderWaybillsList($this->request);
    }


    /**
     * @SWG\Post(path="/integration/iiko/order/create-waybill",
     *     tags={"Integration/iiko/order"},
     *     summary="Создание накладной",
     *     description="Создание накладной",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "org": 1,
     *                              "num_code": 2222,
     *                              "text_code": "Не указано",
     *                              "agent_uuid": "91e0dd93-0923-4509-9435-6cc6224768af",
     *                              "store_id": 777,
     *                              "doc_date": "2018-06-23 09:00:00",
     *                              "note": "New waybill"
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "waybill_id": 2222,
     *              }
     *          )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionCreateWaybill()
    {
        $this->response = $this->container->get('IikoWebApi')->createWaybill($this->request);
    }
}