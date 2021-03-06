<?php

namespace api_web\modules\integration\controllers;

use api_web\classes\IntegrationSettingsWebApi;
use api_web\components\Poster;
use api_web\components\Registry;
use yii\filters\AccessControl;

/**
 * Class SettingController
 *
 * @property IntegrationSettingsWebApi $classWebApi
 * @package api_web\modules\integration\controllers
 */
class SettingController extends \api_web\components\WebApiController
{
    public $className = IntegrationSettingsWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => [
                        'list',
                        'get',
                        'update',
                        'reject-change',
                        'get-main-organizations',
                        'set-main-organizations',
                        'reset-main-org-setting',
                        'get-items-setting',
                        'generate-poster-auth-url',
                        'poster-auth',
                    ],
                    'roles'   => [
                        Registry::MANAGER_RESTAURANT,
                        Registry::BOOKER_RESTAURANT,
                    ],
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
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
     *              {
     *                  "id":27,
     *                  "name":"code",
     *                  "value":"test-123",
     *                  "changed":"test-1234567"
     *              },
     *              {
     *                  "id":23,
     *                  "name":"code",
     *                  "value":"test-123",
     *                  "changed":null
     *              }
     *            }
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
        $this->response = $this->classWebApi->list($this->request);
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
        $this->response = $this->classWebApi->getSetting($this->request);
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
     *                              "value": "10"
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
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->update($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/reject-change",
     *     tags={"Integration/settings"},
     *     summary="Отмена изменение настройки",
     *     description="Отмена изменение настройки",
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
     *                      "setting_id": 2
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": true
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
    public function actionRejectChange()
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->rejectChange($this->request);
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
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->getMainOrganizations($this->request);
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
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->setMainOrganizations($this->request);
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
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->resetMainOrgSetting($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/get-items-setting",
     *     tags={"Integration/settings"},
     *     summary="Список возможных значений для настройки",
     *     description="Список возможных значений для настройки",
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
     *                      "setting_name": "sh_version"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result": { "value":"comment", 4:"Store House 4", 5:"Store House 5"}
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

    public function actionGetItemsSetting()
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->getItemsSetting($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/generate-poster-auth-url",
     *     tags={"Poster"},
     *     summary="Генерация урла для авторизации в Poster",
     *     description="Генерация урла для авторизации в Poster",
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
     *                      "redirect_url": "fronturl.ru"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result":
     *                   "https://joinposter.com/api/auth?application_id=418&redirect_uri=http://api.mixcart.loc/poster-auth&response_type=code"
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
    public function actionGeneratePosterAuthUrl()
    {
        $this->setLicenseServiceId(Registry::POSTER_SERVICE_ID);
        $this->response = (new Poster($this->user->organization_id))->generateAuthUrl($this->request);
    }

    /**
     * @SWG\Post(path="/integration/setting/poster-auth",
     *     tags={"Poster"},
     *     summary="Авторизация по code полученного из oAuth2",
     *     description="Авторизация по code полученного из oAuth2",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "code": 123,
     *                      "account": "qwe",
     *                      "url": "https://backfronturl.ru"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={
     *                  "result": true,
     *                  "data_from_server": {
     *                    "access_token": "145072:7130848db640adf93839a485a4371b3f",
     *                    "account_number": "145072",
     *                    "user": {
     *                        "id": 3,
     *                        "name": "",
     *                        "email": "makagonova@mixcart.ru",
     *                        "role_id": 3
     *                    },
     *                    "ownerInfo": {
     *                        "email": "makagonova@mixcart.ru",
     *                        "phone": "+7 9035445138",
     *                        "city": "",
     *                        "country": "RU",
     *                        "name": "",
     *                        "company_name": "MixCart"
     *                    },
     *                    "tariff": {}
     *                  }
     *              }
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
     * @throws \Exception
     */
    public function actionPosterAuth()
    {
        $this->response = (new Poster($this->user->organization_id))->saveAccessKey($this->request);
    }
}
