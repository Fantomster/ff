<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\iiko\models\iikoService;

class SettingController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/iiko/setting/get",
     *     tags={"Integration/iiko/setting"},
     *     summary="Получение настроек сервиса iiko",
     *     description="Получение сервиса iiko",
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
        $this->response = (new iikoService())->getSettings();
    }

    /**
     * @SWG\Post(path="/integration/iiko/setting/set",
     *     tags={"Integration/iiko/setting"},
     *     summary="Установка настроек сервиса iiko",
     *     description="Установка настроек сервиса iiko",
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
        $this->response = (new iikoService())->setSettings($this->request);
    }

}