<?php

namespace api_web\controllers;

use api_web\classes\AnalyticsWebApi;
use api_web\components\WebApiController;

/**
 * Class AnalyticsController
 *
 * @property AnalyticsWebApi $classWebApi
 * @package api_web\controllers
 */
class AnalyticsController extends WebApiController
{

    public $className = AnalyticsWebApi::class;

    /**
     * @SWG\Post(path="/analytics/client-goods",
     *     tags={"Analytics"},
     *     summary="Ресторан: Статистика по товарам",
     *     description="Ресторан: Статистика по товарам",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "search": {
     *                         "vendor_id": {
     *                             124,
     *                             143
     *                         },
     *                         "employee_id": 21,
     *                         "order_status_id": {
     *                             4,
     *                             5
     *                         },
     *                         "currency_id": 1,
     *                         "date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                     },
     *                     "pagination": {
     *                         "page": 1,
     *                         "page_size": 12
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "name": "Товар 11",
     *                          "count": 21.00,
     *                          "total": 10200.01,
     *                          "currency_id": 1,
     *                          "currency": "RUB"
     *                      },
     *                      {
     *                          "name": "Товар 12",
     *                          "count": 32.01,
     *                          "total": 1021,
     *                          "currency_id": 1,
     *                          "currency": "RUB"
     *                      },
     *                      {
     *                          "name": "Товар 32",
     *                          "count": 132.12,
     *                          "total": 102,
     *                          "currency_id": 1,
     *                          "currency": "RUB"
     *                      }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "total_page": 17,
     *                      "page_size": 12
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionClientGoods()
    {
        $this->response = $this->classWebApi->clientGoods($this->request);
    }

    /**
     * @SWG\Post(path="/analytics/client-purchases",
     *     tags={"Analytics"},
     *     summary="Ресторан: Объем закупок за период",
     *     description="Ресторан: Объем закупок за период",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "search": {
     *                         "vendor_id": {
     *                             124,
     *                             143
     *                         },
     *                         "employee_id": 21,
     *                         "order_status_id": {
     *                             4,
     *                             5
     *                         },
     *                         "currency_id": 1,
     *                         "date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "total_sum": 69521.02,
     *                          "date": "2017-10-06T03:00:00+03:00",
     *                          "raw_date": "2017-10-06T23:00:00+03:00"
     *                      },
     *                      {
     *                          "total_sum": 6952321,
     *                          "date": "2017-10-06T03:00:00+03:00",
     *                          "raw_date": "2017-10-06T23:00:00+03:00"
     *                      },
     *                      {
     *                          "total_sum": 6952321.1,
     *                          "date": "2017-10-06T03:00:00+03:00",
     *                          "raw_date": "2017-10-06T23:00:00+03:00"
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionClientPurchases()
    {
        $this->response = $this->classWebApi->clientPurchases($this->request);
    }

    /**
     * @SWG\Post(path="/analytics/client-orders",
     *     tags={"Analytics"},
     *     summary="Ресторан: Статистика по поставщикам",
     *     description="Ресторан: Статистика по поставщикам",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "search": {
     *                         "vendor_id": {
     *                             124,
     *                             143
     *                         },
     *                         "employee_id": 21,
     *                         "order_status_id": {
     *                             4,
     *                             5
     *                         },
     *                         "currency_id": 1,
     *                         "date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "name": "Поставщик 12",
     *                          "total_sum": 695025,
     *                          "total_count_order": 2323
     *                      },
     *                      {
     *                          "name": "Поставщик 355",
     *                          "total_sum": 292123,
     *                          "total_count_order": 21
     *                      },
     *                      {
     *                          "name": "Поставщик 231",
     *                          "total_sum": 13001,
     *                          "total_count_order": 1
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionClientOrders()
    {
        $this->response = $this->classWebApi->clientOrders($this->request);
    }

    /**
     * @SWG\Post(path="/analytics/client-vendors",
     *     tags={"Analytics"},
     *     summary="Ресторан: Объем по поставщикам",
     *     description="Ресторан: Объем по поставщикам",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "search": {
     *                         "vendor_id": {
     *                             124,
     *                             143
     *                         },
     *                         "employee_id": 21,
     *                         "order_status_id": {
     *                             4,
     *                             5
     *                         },
     *                         "currency_id": 1,
     *                         "date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "name": "Поставщик 12",
     *                          "total_sum": 695025,
     *                          "total_count_order": 12,
     *                          "percent_sum": 69.5
     *                      },
     *                      {
     *                          "name": "Поставщик 355",
     *                          "total_sum": 292123,
     *                          "total_count_order": 21,
     *                          "percent_sum": 29.2
     *                      },
     *                      {
     *                          "name": "Поставщик 231",
     *                          "total_sum": 13001,
     *                          "total_count_order": 1,
     *                          "percent_sum": 1.3
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionClientVendors()
    {
        $this->response = $this->classWebApi->clientVendors($this->request);
    }


    /**
     * @SWG\Post(path="/analytics/client-summary",
     *     tags={"Analytics"},
     *     summary="Ресторан: Общая аналитика",
     *     description="Ресторан: Общая аналитика",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "search": {
     *                         "vendor_id": {
     *                             124,
     *                             143
     *                         },
     *                         "employee_id": 21,
     *                         "order_status_id": {
     *                             4,
     *                             5
     *                         },
     *                         "currency_id": 1,
     *                         "date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "new": 10,
     *                          "process": 13,
     *                          "done": 29,
     *                          "total_sum": 1212.12,
     *                          "currency_id": 1,
     *                          "currency": "RUB"
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionClientSummary()
    {
        $this->response = $this->classWebApi->clientSummary($this->request);
    }


    /**
     * @SWG\Post(path="/analytics/currencies",
     *     tags={"Analytics"},
     *     summary="Метод получения списка валют",
     *     description="Метод получения списка валют",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "currency_id": 3,
     *                          "iso_code": "RUB"
     *                      },
     *                      {
     *                          "currency_id": 2,
     *                          "iso_code": "USD"
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionCurrencies()
    {
        $this->response = $this->classWebApi->currencies();
    }

}
