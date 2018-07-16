<?php

namespace api_web\modules\integration\modules\one_s\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\one_s\models\one_sService;

class SettingController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/one_s/setting/get",
     *     tags={"Integration/one_s/setting"},
     *     summary="Получение настроек сервиса one_s",
     *     description="Получение сервиса one_s",
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
        $this->response = (new one_sService())->getSettings();
    }

    /**
     * @SWG\Post(path="/integration/one_s/setting/set",
     *     tags={"Integration/one_s/setting"},
     *     summary="Установка настроек сервиса one_s",
     *     description="Установка настроек сервиса one_s",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"taxVat":0, "auth_login":"admin"}
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
        $this->response = (new one_sService())->setSettings($this->request);
    }

}