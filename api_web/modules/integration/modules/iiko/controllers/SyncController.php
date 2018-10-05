<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\classes\sync\ServiceIiko;
use api_web\modules\integration\modules\iiko\models\iikoSync;
use yii\web\BadRequestHttpException;

class SyncController extends WebApiController
{
     #Синхронизация iiko
     /**
     * @SWG\Post(path="/integration/iiko/sync/run",
     *     tags={"OLD  Integration_iiko"},
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
     *     tags={"OLD  Integration_iiko"},
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
     * @SWG\Post(path="/integration/iiko/sync/send-waybill",
     *     tags={"/integration/iiko/"},
     *     summary="Метод отправки накладных в Iiko",
     *     description="Метод отправки накладных в Iiko",
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
     *                      "ids": {
     *                          1,
     *                          2,
     *                          3,
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       "1": true,
     *                       "2": true,
     *                       "3": false,
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
     * @throws \Exception
     */
    public function actionSendWaybill()
    {
        $this->response = (new ServiceIiko('iiko', 2))->sendWaybill($this->request);
    }


}