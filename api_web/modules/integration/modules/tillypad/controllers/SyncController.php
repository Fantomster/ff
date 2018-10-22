<?php

namespace api_web\modules\integration\modules\tillypad\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\classes\sync\ServiceTillypad;
use api_web\modules\integration\modules\tillypad\models\TillypadSync;
use yii\web\BadRequestHttpException;

class SyncController extends WebApiController
{
    #Синхронизация iiko
    /**
     * @SWG\Post(path="/integration/tillypad/sync/run",
     *     tags={"OLD  Integration_Tillypad"},
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
     * @SWG\Post(path="/integration/tillypad/sync/list",
     *     tags={"OLD  Integration_Tillypad"},
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
        $this->response = (new TillypadSync())->list();
    }

    /**
     * @SWG\Post(path="/integration/tillypad/sync/send-waybill",
     *     tags={"OLD Integration_Tillypad"},
     *     summary="Метод отправки накладных в Tillypad",
     *     description="Метод отправки накладных в Tillypad",
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
        $this->response = (new ServiceTillypad('Tillypad', 10))->sendWaybill($this->request);
    }

}