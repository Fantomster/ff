<?php

/**
 * Class EdoController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-11
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class EdoController
 * @package api_web\controllers
 */
class EdoController extends WebApiController
{

    /**
     * @SWG\Post(path="/edo/order-history",
     *     tags={"Edo"},
     *     summary="Данные заказов в системе EDI",
     *     description="Данные заказов в системе EDI",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "search": {
     *                         "vendor": {
     *                             124,
     *                             143
     *                         },
     *                         "status": {
     *                             1,
     *                             2
     *                         },
     *                         "create_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         },
     *                         "completion_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "id"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "orders": {
     *                      {
     *                          "id": 12,
     *                          "created_at": "10.10.2016",
     *                          "status_updated_at": "10.10.2016",
     *                          "status": 1,
     *                          "status_text": "Ожидает подтверждения поставщика",
     *                          "vendor": "POSTAVOK.NET CORPORATION",
     *                          "create_user": "Admin",
     *                          "comment": "Коментарий утерян в ящике стола"
     *                      },
     *                      {
     *                          "id": 14,
     *                          "created_at": "10.10.2016",
     *                          "status_updated_at": "11.10.2016",
     *                          "status": 1,
     *                          "status_text": "Ожидает подтверждения поставщика",
     *                          "vendor": "POSTAVKA CORP INC",
     *                          "create_user": "Vasya",
     *                          "comment": "Коментариев гнет"
     *                      }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 17
     *                  },
     *                  "pagination": "-name"
     *              }
     *         )
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
    public function actionOrderHistory()
    {
        $this->response = $this->container->get('EdoWebApi')->orderHistory($this->request);
    }

}