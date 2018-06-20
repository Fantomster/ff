<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\rkeeper\models\rkeeperService;

class SettingController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/rkeeper/setting/get",
     *     tags={"Integration/rkeeper/setting"},
     *     summary="Получение настроек сервиса rkeeper",
     *     description="Получение сервиса rkeeper",
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
    public function actionGet()
    {
        $this->response = (new rkeeperService())->getSettings();
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/setting/set",
     *     tags={"Integration/rkeeper/setting"},
     *     summary="Установка настроек сервиса rkeeper",
     *     description="Установка настроек сервиса rkeeper",
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
    public function actionSet()
    {
        $this->response = (new rkeeperService())->setSettings($this->request);
    }

}