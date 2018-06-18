<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;

class OrderController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/iiko/order/list",
     *     tags={"Integration/iiko"},
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
     *                  default={}
     *              )
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
    public function actionList()
    {
        $this->response = ['testList - iiko'];
    }


    /**
     * @SWG\Post(path="/integration/iiko/order/order-waybills-list",
     *     tags={"Integration/iiko"},
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
    public function actionOrderWaybillsList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->getOrderWaybillsList($this->request);
    }

}