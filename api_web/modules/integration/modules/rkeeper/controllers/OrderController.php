<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\classes\RkeeperWebApi;
use api_web\components\WebApiController;
use api_web\modules\integration\modules\rkeeper\models\rkeeperOrder;

/**
 * Class OrderController
 *
 * @property RkeeperWebApi $classWebApi
 * @package api_web\modules\integration\modules\rkeeper\controllers
 */
class OrderController extends WebApiController
{
    public $className = RkeeperWebApi::class;

    /**
     * @SWG\Post(path="/integration/rkeeper/order/list",
     *     tags={"Integration/rkeeper/order"},
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
     *                                  "store_rid": "Не указано",
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
     * @throws
     */
    public function actionList()
    {
        $this->response = $this->classWebApi->getCompletedOrdersList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/order/waybills-list",
     *     tags={"Integration/rkeeper/order"},
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
     * @throws
     */
    public function actionWaybillsList()
    {
        $this->response = $this->classWebApi->getOrderWaybillsList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/order/get-waybill",
     *     tags={"Integration/rkeeper/order"},
     *     summary="Информация о накладной",
     *     description="Информация о накладной",
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
     *                      "waybill_id":1
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       {
     *                          "waybill_id": 2,
     *                          "order_id": 3899,
     *                          "num_code": 2222,
     *                          "agent_denom": "Не указано",
     *                          "store_denom": "Не указано",
     *                          "doc_date": "23 июня 2018 г.",
     *                          "status_denom": "Выгружена",
     *                          "data": {
     *                              {
     *                                  "mixcart_product_id": 4822,
     *                                  "mixcart_product_name": "Продукт 39",
     *                                  "mixcart_product_ed": "кг",
     *                                  "iiko_product_id": 1,
     *                                  "iiko_product_name": "Бананы",
     *                                  "iiko_product_ed": "кг",
     *                                  "order_position_count": 9999999.999,
     *                                  "iiko_position_count": 9999999.999,
     *                                  "koef": 1,
     *                                  "sum_without_nds": 333299999.97,
     *                                  "sum": 333299999.97,
     *                                  "nds": 0
     *                              }
     *                          }
     *                      }
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
     * @throws
     */
    public function actionGetWaybill()
    {
        $this->response = (new rkeeperOrder())->getWaybill($this->request);
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/order/create-waybill",
     *     tags={"Integration/rkeeper/order"},
     *     summary="Создание накладной к заказу",
     *     description="Создание накладной к заказу",
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
     *                              "order_id": 1,
     *                              "num_code": 2222,
     *                              "text_code": "Не указано",
     *                              "agent_rid": 4425,
     *                              "store_rid": 777,
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
     *                "waybill_id": 1,
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
     * @throws
     */
    public function actionCreateWaybill()
    {
        $this->response = $this->classWebApi->handleWaybill($this->request);
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/order/update-waybill",
     *     tags={"Integration/rkeeper/order"},
     *     summary="Редактирование накладной к заказу",
     *     description="Редактирование накладной к заказу",
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
     *                              "waybill_id": 1,
     *                              "num_code": 2222,
     *                              "text_code": "Не указано",
     *                              "agent_rid": 4425,
     *                              "store_rid": 777,
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
     *                "waybill_id": 1,
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
     * @throws
     */
    public function actionUpdateWaybill()
    {
        $this->response = $this->classWebApi->handleWaybill($this->request);
    }

}
