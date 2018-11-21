<?php

namespace api_web\modules\integration\controllers;

class SettingController extends \api_web\components\WebApiController
{
    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->license_service_id = $this->user->integration_service_id ?? 0;
        return parent::beforeAction($action);
    }

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

    /**
     * @SWG\Post(path="/integration/setting/get-main-organizations",
     *     tags={"Integration/settings"},
     *     summary="Получение настройки главного бизнеса для дочерних",
     *     description="Получение настройки главного бизнеса для дочерних",
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
     *                   "result": {
     *                        {
     *                            "id": "4300",
     *                            "parent_id": "4398",
     *                            "name": "1йцу",
     *                            "main_org":true,
     *                            "checked": false
     *                        },
     *                        {
     *                            "id": "4392",
     *                            "parent_id": "4398",
     *                            "name": "тест сортировка",
     *                            "main_org":false,
     *                            "checked":true
     *                        },
     *                        {
     *                            "id": "4400",
     *                            "parent_id": "4398",
     *                            "name": "421",
     *                            "main_org":false,
     *                            "checked":false
     *                        }
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
    public function actionGetMainOrganizations()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->getMainOrganizations($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/set-main-organizations",
     *     tags={"Integration/settings"},
     *     summary="Изменение настройки главного бизнеса для дочерних",
     *     description="Изменение настройки главного бизнеса для дочерних",
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
     *                      "service_id":2,
     *                      "main_org": 4300,
     *                      "checked":{
     *                          4433,
     *                          4432,
     *                          4431,
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
     *                   "result": {
     *                      "4431": true,
     *                      "4432": true,
     *                      "4433": true
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

    public function actionSetMainOrganizations()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->setMainOrganizations($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/reset-main-org-setting",
     *     tags={"Integration/settings"},
     *     summary="Сброс настройки главного бизнеса для дочерних",
     *     description="Сброс настройки главного бизнеса для дочерних",
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
     *                      "service_id":2
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result": false
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

    public function actionResetMainOrgSetting()
    {
        $this->response = $this->container->get('IntegrationSettingsWebApi')->resetMainOrgSetting($this->request);
    }
}