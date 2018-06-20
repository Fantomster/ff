<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\components\WebApiController;

class OrderController extends WebApiController
{
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
        $this->response = $this->container->get('RkeeperWebApi')->getCompletedOrdersList($this->request);
    }

}