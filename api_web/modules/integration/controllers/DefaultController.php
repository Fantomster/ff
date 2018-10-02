<?php

namespace api_web\modules\integration\controllers;

class DefaultController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/default/service-list",
     *     tags={"Integration"},
     *     summary="Список сервисов интерграции",
     *     description="Список сервисов интерграции",
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
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationServiceList"),
     *     )
     * )
     */
    public function actionServiceList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->list($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/create-waybill",
     *     tags={"Integration"},
     *     summary="Создание накладной к заказу или в конкретном сервисе у.с",
     *     description="Создание накладной к заказу или в конкретном сервисе у.с",
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
     *                              "service_id": 1,
     *                              "acquirer_id": 1,
     *                              "bill_status_id": 1,
     *                              "outer_number_code": "345663-6454-4657-234775",
     *                              "outer_number_additional": "345663-6454-4657-234775",
     *                              "outer_store_uuid": "345663-6454-4657-234775",
     *                              "outer_contractor_uuid": "545663-6454-4657-234775",
     *                              "vat_included": 1,
     *                              "outer_duedate": "2018-06-23 09:00:00",
     *                              "outer_order_date": "2018-06-23 09:00:00",
     *                              "doc_date": "2018-06-23 09:00:00",
     *                              "outer_note": "New waybill",
     *                              "data": {
     *                                  {
     *                                      "order_content_id": 14822,
     *                                      "product_outer_id": 4822,
     *                                      "quantity_waybill": 1,
     *                                      "merc_uuid": "745663-6454-4657-234775",
     *                                      "price_with_vat": 333299999.97,
     *                                      "price_without_vat": 333299999.97,
     *                                      "sum_without_vat": 333299999.97,
     *                                      "sum_with_vat": 333299999.97,
     *                                      "vat_waybill": 0
     *                                  }
     *                              }
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
     */
    public function actionCreateWaybill()
    {
        $this->response = $this->container->get('IntegrationWebApi')->handleWaybill($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/reset-waybill-content",
     *     tags={"Integration"},
     *     summary="Сброс данных позиции, на значения из заказа",
     *     description="Сброс данных позиции, на значения из заказа",
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
     *                              "waybill_content_id": 14822
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
     */
    public function actionResetWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->resetWaybillContent($this->request);
    }
}