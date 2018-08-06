<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\rkeeper\models\rkeeperSync;


use yii\web\BadRequestHttpException;

class SyncController extends WebApiController
{
    #Синхронизация r-keeper
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
        if (empty($this->request['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }

        $class = null;
        $method = null;

        switch ($this->request['type']) {
            case 1:
                $class = \frontend\modules\clientintegr\modules\rkws\components\AgentHelper::className();
                $method = "getAgents";
                break;
            case 2:
                $class = \frontend\modules\clientintegr\modules\rkws\components\StoreHelper::className();
                $method = "getStore";
                break;
            case 3:
                $class = \frontend\modules\clientintegr\modules\rkws\components\ProductHelper::className();
                $method = "getProduct";
                break;
            case 4:
                $class = \frontend\modules\clientintegr\modules\rkws\components\EdismHelper::className();
                $method = "getEdism";
                break;
            case 5:
                $class = \frontend\modules\clientintegr\modules\rkws\components\ProductgroupHelper::className();
                $method = "getCategory";
                break;
        }

        if ($class === null OR $method === null) {
            throw new BadRequestHttpException("Type not found");
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $res = new $class();
            ob_start();
            $res->{$method}();
            $error = ob_get_clean();
            if (!empty($error)) {
                throw new BadRequestHttpException($error);
            }
            $transaction->commit();
            $this->response = ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
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