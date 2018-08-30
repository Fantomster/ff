<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class AnalyticsController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-28
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class AnalyticsController extends WebApiController
{

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
     *                          "total": 102,
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
     */
    public function actionClientGoods()
    {
        $this->response = $this->container->get('AnalyticsWebApi')->clientGoods($this->request);
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
     *                          "name": "Поставщик 1",
     *                          "total_sum": 523801,
     *                          "total_count_order": 400,
     *                      },
     *                      {
     *                          "name": "Поставщик 231",
     *                          "total_sum": 3801,
     *                          "total_count_order": 23,
     *                      },
     *                      {
     *                          "name": "Поставщик 3",
     *                          "total_sum": 523803,
     *                          "total_count_order": 12,
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
     */
    public function actionClientOrders()
    {
        $this->response = $this->container->get('AnalyticsWebApi')->clientOrders($this->request);
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
     *                          "name": "Поставщик 1",
     *                          "total_sum": 523801,
     *                      },
     *                      {
     *                          "name": "Поставщик 231",
     *                          "total_sum": 3801,
     *                      },
     *                      {
     *                          "name": "Поставщик 3",
     *                          "total_sum": 523803,
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
     */
    public function actionClientPurchases()
    {
        $this->response = $this->container->get('AnalyticsWebApi')->clientPurchases($this->request);
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
     *                          "name": "Поставщик 1",
     *                          "total_sum": 523801,
     *                      },
     *                      {
     *                          "name": "Поставщик 231",
     *                          "total_sum": 3801,
     *                      },
     *                      {
     *                          "name": "Поставщик 3",
     *                          "total_sum": 523803,
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
     */
    public function actionClientVendors()
    {
        $this->response = $this->container->get('AnalyticsWebApi')->clientVendors($this->request);
    }

}