<?php

namespace api_web\modules\integration\controllers;

class SettingController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/setting/list",
     *     tags={"Integration/settings"},
     *     summary="Список настроек интеграции",
     *     description="Список настроек интеграции",
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
     *                      "service_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "name":"value",
     *                  "name1":"value1",
     *                  "name2":"value2"
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
     * )
     * @throws \Exception
     */

    public function actionList()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->list($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/get",
     *     tags={"Integration/settings"},
     *     summary="Список настройки интеграции по ее названию",
     *     description="Список настройки интеграции по ее названию",
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
     *                      "service_id": 1,
     *                      "name": "taxvat"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "taxVat":18,
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
     * )
     * @throws \Exception
     */

    public function actionGet()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->getSetting($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/update",
     *     tags={"Integration/settings"},
     *     summary="Изменение настройки",
     *     description="Изменение настройки",
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
     *                      "service_id": 1,
     *                      "settings": {
     *                           {
     *                              "name":"taxVat",
     *                              "value":10
     *                          },
     *                           {
     *                              "name":"auth_password",
     *                              "value":"password"
     *                          }
     *                       }
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  {
     *                   "taxVat":10,
     *                   "auth_password": {
     *                          "error": "Setting not found",
     *                          }
     *                   }
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
     * )
     * @throws \Exception
     */

    public function actionUpdate()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->update($this->request);
    }
}