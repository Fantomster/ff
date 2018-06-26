<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\iiko\models\iikoSync;
use yii\web\BadRequestHttpException;

class SyncController extends WebApiController
{
     #Синхронизация iiko
     /**
     * @SWG\Post(path="/integration/iiko/sync/run",
     *     tags={"Integration/iiko/sync"},
     *     summary="Запуск синхронизации",
     *     description="Запуск синхронизации",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"type": 2}
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
    public function actionRun()
    {
        if (empty($this->request['type'])) {
            throw new BadRequestHttpException('Empty type');
        }

        $this->response = (new iikoSync())->run($this->request['type']);
    }


    /**
     * @SWG\Post(path="/integration/iiko/sync/list",
     *     tags={"Integration/iiko/sync"},
     *     summary="Список синхронизируемых справочников",
     *     description="Список синхронизируемых справочников",
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
        $this->response = (new iikoSync())->list();
    }


    /**
     * @SWG\Post(path="/integration/iiko/sync/create-waybill-data",
     *     tags={"Integration/iiko/sync"},
     *     summary="Создание сопоставлений номенклатуры накладной с продуктами MixCart",
     *     description="Создание сопоставлений номенклатуры накладной с продуктами MixCart",
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
     *                              "product_id": 2222,
     *                              "product_rid": 2222,
     *                              "munit": "кг",
     *                              "org": 2222,
     *                              "vat": 1000,
     *                              "vat_included": 1180,
     *                              "sum": "10000,00",
     *                              "quant": "5,67",
     *                              "defsum": 10000.00,
     *                              "defquant": 12000.00,
     *                              "koef": "1,00",
     *                              "linked_at": "2018-06-20 18:09:01"
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
     *                "waybill_data_id": 1,
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
    public function actionCreateWaybillData()
    {
        $this->response = $this->container->get('IikoWebApi')->handleWaybillData($this->request);
    }
}