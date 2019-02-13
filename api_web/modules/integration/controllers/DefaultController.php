<?php

namespace api_web\modules\integration\controllers;

use api_web\classes\IntegrationWebApi;
use api_web\components\Registry;
use api_web\modules\integration\classes\SyncServiceFactory;
use yii\filters\AccessControl;

/**
 * Class DefaultController
 *
 * @property IntegrationWebApi $classWebApi
 * @package api_web\modules\integration\controllers
 */
class DefaultController extends \api_web\components\WebApiController
{
    public $className = IntegrationWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'map-list',
                        'map-update',
                        'service-list',
                    ],
                    'roles'      => [
                        Registry::MANAGER_RESTAURANT,
                        Registry::BOOKER_RESTAURANT,
                    ],
                    'roleParams' => [
                        'user' => $this->user
                    ]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'user-service-set',
                        'check-connect',
                    ],
                    'roles'      => [
                        Registry::OPERATOR
                    ],
                    'roleParams' => [
                        'user' => $this->user
                    ]
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/integration/default/service-list",
     *     tags={"Integration"},
     *     summary="Список сервисов интерграции",
     *     description="Список сервисов интерграции",
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
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationServiceList"),
     *     )
     * )
     * @throws \Exception
     */
    public function actionServiceList()
    {
        $this->response = $this->classWebApi->list();
    }

    /**
     * @SWG\Post(path="/integration/default/user-service-set",
     *     tags={"Integration"},
     *     summary="Установить ИД интеграции по умолчанию для юзера",
     *     description="Установить ИД интеграцию по умолчанию для юзера",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"service_id":2}
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
    public function actionUserServiceSet()
    {
        $this->response = $this->classWebApi->userServiceSet($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/map-list",
     *     tags={"Integration"},
     *     summary="Получение списка сопоставления со всеми связями ",
     *     description="Получение списка сопоставления со всеми связями ",
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
     *                      "business_id": 1,
     *                      "search": {
     *                              "product": "Апельсины",
     *                              "vendor": 1,
     *                              "product_id": 2,
     *                       },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
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
     *                  "products" : {
     *                       {
     *                          "id": "1",
     *                          "service_id": 1,
     *                          "organization_id": 1,
     *                          "product": {
     *                              "id": 481037,
     *                             "name": "ааа"
     *                          },
     *                          "unit": "бутылка",
     *                          "vendor": {
     *                              "id": 3803,
     *                              "name": "EL Поставщик"
     *                          },
     *                          "outer_product": {
     *                              "id": 10,
     *                              "name": "Авокадо"
     *                          },
     *                          "outer_unit": {
     *                              "id": 4,
     *                              "name": "шт"
     *                          },
     *                          "outer_store": {
     *                              "id": 3,
     *                              "name": "Тест склад 1"
     *                          },
     *                          "coefficient": 1,
     *                          "vat": 10,
     *                          "created_at": "2018-11-01T19:03:31+03:00",
     *                          "updated_at": "2018-11-01T19:03:39+03:00"
     *                       }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 17
     *                  },
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
    public function actionMapList()
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->getProductMapList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/map-update",
     *     tags={"Integration"},
     *     summary="Изменение атрибутов сопоставления",
     *     description="Изменение атрибутов сопоставления",
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
     *                          "business_id": 1,
     *                          "service_id": 2,
     *                          "map": {
     *                              {
     *                                  "product_id": 2234,
     *                                  "outer_product_id": 102,
     *                                  "outer_store_id": 1056,
     *                                  "coefficient": 2,
     *                                  "vat": 10
     *                              },
     *                              {
     *                                  "product_id": 4234,
     *                                  "outer_product_id": 132,
     *                                  "outer_store_id": 2076,
     *                                  "coefficient": 1,
     *                                  "vat": 18
     *                              }
     *                          }
     *                       }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "2234" : {
     *                      "success": true,
     *                       },
     *                  "4234": {
     *                      "success": false,
     *                      "error": "text error",
     *                  },
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
    public function actionMapUpdate()
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = $this->classWebApi->mapUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/check-connect",
     *     tags={"Integration"},
     *     summary="Проверка, доступно ли подключение к серверу интеграции",
     *     description="Проверка, доступно ли подключение к серверу интеграции, если params пустой, будут браться
     *     настройки из базы", produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                          "service_id": 2,
     *                          "params":{
     *                              "URL":"http://192.168.100.39:8080/resto/api",
     *                              "auth_login":"admin",
     *                              "auth_password":"password",
     *                           }
     *                       }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={ "result":true }
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
    public function actionCheckConnect()
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        $this->response = SyncServiceFactory::init($this->request['service_id'])->checkConnect($this->request);
    }
}
