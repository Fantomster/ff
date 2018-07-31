<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\rkeeper\models\rkeeperSync;

class SyncController extends WebApiController
{
     #Синхронизация iiko
     /**
     * @SWG\Post(path="/integration/rkeeper/sync/run",
     *     tags={"Integration/rkeeper/sync"},
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
        return true;
    }


    /**
     * @SWG\Post(path="/integration/rkeeper/sync/list",
     *     tags={"Integration/rkeeper/sync"},
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
        $this->response = (new rkeeperSync())->list();
    }
}