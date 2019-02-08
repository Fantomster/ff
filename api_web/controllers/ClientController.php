<?php

namespace api_web\controllers;

use api_web\classes\ClientWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use common\models\licenses\License;
use yii\filters\AccessControl;

/**
 * Class ClientController
 *
 * @property ClientWebApi $classWebApi
 * @package api_web\controllers
 */
class ClientController extends WebApiController
{
    public $className = ClientWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => [
                        'detail-update-logo',
                        'employee-create',
                        'employee-update',
                        'employee-delete',
                        'notification-update',
                        'additional-email-create',
                        'additional-email-delete',
                    ],
                    'roles'   => [Registry::ADMINISTRATOR_RESTAURANT],
                ],
                [
                    'allow'   => true,
                    'actions' => [
                        'detail-update',
                        'detail',
                        'nds-country-list',
                        'notification-list',
                        'employee-get',
                        'employee-list',
                        'employee-search',
                        'employee-roles',
                        'get-license-mix-cart',
                    ],
                    'roles'   => [
                        Registry::OPERATOR,
                        Registry::BOOKER_RESTAURANT
                    ],
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * Список методов которые не нужно логировать
     * Можно выключать передачу файлов в base64, так как бывает очень жирные файлы попадаются
     *
     * @var array
     */
    public $not_log_actions = [
        'detail-update-logo'
    ];

    /**
     * @SWG\Post(path="/client/detail",
     *     tags={"Client"},
     *     summary="Данные ресторана",
     *     description="Данные ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                          "id": 1,
     *                          "name": "Космическая пятница",
     *                          "legal_entity": "ООО 'Космическая пятница'",
     *                          "contact_name": "Космический Чел",
     *                          "phone": "+7 9279279279",
     *                          "email": "investor@f-keeper.ru",
     *                          "site": "mixcart.ru",
     *                          "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                          "image":
     *                          "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
     *                          "type_id": 1,
     *                          "type": "Ресторан",
     *                          "rating": 0,
     *                          "house": "50А",
     *                          "route": "Бакалейная улица",
     *                          "city": "Казань",
     *                          "administrative_area_level_1": "Республика Татарстан",
     *                          "country": "Россия",
     *                          "about": "Вот контора так контора",
     *                          "nds_country": {
     *                              "uuid": "72a84b51-5c5e-11e1-b9b7-001966f192f1",
     *                              "name": "Российская Федерация"
     *                          }
     *             }
     *          ),
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
    public function actionDetail()
    {
        $this->response = $this->classWebApi->detail();
    }

    /**
     * @SWG\Post(path="/client/detail-update",
     *     tags={"Client"},
     *     summary="Обновление данных ресторана",
     *     description="Обновление данных ресторана",
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
     *                             "name": "Космическая пятница",
     *                             "legal_entity": "ООО 'Космическая пятница'",
     *                             "contact_name": "Космический Чел",
     *                             "phone": "+79182225588",
     *                             "email":"test@test.ru",
     *                             "about": "Вот контора так контора",
     *                             "is_allowed_for_franchisee": 1,
     *                             "gmt": 3,
     *                             "nds_country_uuid": "72a84b51-5c5e-11e1-b9b7-001966f192f1",
     *                             "address": {
     *                                  "country":"Россия",
     *                                  "region": "Московская область",
     *                                  "locality": "Люберцы",
     *                                  "route": "улица Побратимов",
     *                                  "house": "владение 107",
     *                                  "lat": 55.7713,
     *                                  "lng": 37.7055,
     *                                  "place_id":"ChIJM4NYCODJSkERVeMzXqoIJho"
     *                             }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/Organization"),
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
    public function actionDetailUpdate()
    {
        $this->response = $this->classWebApi->detailUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/client/detail-update-logo",
     *     tags={"Client"},
     *     summary="Обновление лого ресторана",
     *     description="Обновление лого ресторана",
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
     *                      "image_source": "data:image/png;base64,iVBORw0KGgoAA=="
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                          "id": 1,
     *                          "name": "Космическая пятница",
     *                          "legal_entity": "ООО 'Космическая пятница'",
     *                          "contact_name": "Космический Чел",
     *                          "phone": "+7 9279279279",
     *                          "email": "investor@f-keeper.ru",
     *                          "site": "mixcart.ru",
     *                          "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                          "image":
     *                          "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
     *                          "type_id": 1,
     *                          "type": "Ресторан",
     *                          "rating": 0,
     *                          "house": "50А",
     *                          "route": "Бакалейная улица",
     *                          "city": "Казань",
     *                          "administrative_area_level_1": "Республика Татарстан",
     *                          "country": "Россия",
     *                          "about": "Вот контора так контора"
     *             }
     *          ),
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
    public function actionDetailUpdateLogo()
    {
        $this->response = $this->classWebApi->detailUpdateLogo($this->request);
    }

    /**
     * @SWG\Post(path="/client/nds-country-list",
     *     tags={"Client"},
     *     summary="Список стран для настроек НДС ресторана",
     *     description="Список стран для настроек НДС ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "items": {
     *                      {
     *                          "uuid": "72a84b51-5c5e-11e1-b9b7-001966f192f1",
     *                          "name": "Российская Федерация"
     *                      },
     *                      {
     *                          "uuid": "388beb78-47c5-1f4c-8875-aa5ebf3a3671",
     *                          "name": "Абхазия"
     *                      }
     *                  }
     *             }
     *          ),
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
    public function actionNdsCountryList()
    {
        $this->response = $this->classWebApi->ndsCountryList();
    }

    /**
     * @SWG\Post(path="/client/notification-list",
     *     tags={"Client/Notification"},
     *     summary="Список дополнительных email адресом для уведомлений",
     *     description="Список дополнительных email адресом для уведомлений",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={ }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  {
     *                      "id": 13963,
     *                      "value": "+79162221111",
     *                      "type": "user_phone",
     *                      "order_created": 0,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 0,
     *                      "request_accept": 0
     *                  },
     *                  {
     *                      "id": 3983,
     *                      "value": "neo@neo.com",
     *                      "type": "user_email",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 1,
     *                      "request_accept": 1
     *                  },
     *                  {
     *                      "id": 2,
     *                      "value": "email123@email.ru",
     *                      "type": "additional_email",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 1,
     *                      "request_accept": 1
     *                  }
     *              }
     *          ),
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
    public function actionNotificationList()
    {
        $this->response = $this->classWebApi->notificationList();
    }

    /**
     * @SWG\Post(path="/client/notification-update",
     *     tags={"Client/Notification"},
     *     summary="Обновление настроек уведомлений",
     *     description="Обновление настроек уведомлений",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{
     *                      "id": 2,
     *                      "type": "additional_email",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 0,
     *                      "order_done": 1,
     *                      "request_accept": 0
     *                  }}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  {
     *                      "id": 13963,
     *                      "value": "+79162221111",
     *                      "type": "user_phone",
     *                      "order_created": 0,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 0,
     *                      "request_accept": 0
     *                  },
     *                  {
     *                      "id": 3983,
     *                      "value": "neo@neo.com",
     *                      "type": "user_email",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 1,
     *                      "request_accept": 1
     *                  },
     *                  {
     *                      "id": 2,
     *                      "value": "email123@email.ru",
     *                      "type": "additional_email",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 1,
     *                      "order_done": 1,
     *                      "request_accept": 1
     *                  }
     *              }
     *          ),
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
    public function actionNotificationUpdate()
    {
        $this->response = $this->classWebApi->notificationUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/client/additional-email-create",
     *     tags={"Client/Notification"},
     *     summary="Создать дополнительный email адрес для уведомлений",
     *     description="Создать дополнительный email адрес для уведомлений",
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
     *                      "email": "email@email.ru",
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 0,
     *                      "order_done": 1,
     *                      "request_accept": 0
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "id": 2,
     *                      "email": "email@email.ru",
     *                      "organization_id": 1,
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 0,
     *                      "order_done": 1,
     *                      "request_accept": 0
     *              }
     *          ),
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
    public function actionAdditionalEmailCreate()
    {
        $this->response = $this->classWebApi->additionalEmailCreate($this->request);
    }

    /**
     * @SWG\Post(path="/client/additional-email-delete",
     *     tags={"Client/Notification"},
     *     summary="Удалить дополнительный email адрес для уведомлений",
     *     description="Удалить дополнительный email адрес для уведомлений",
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
     *                      "id": 2
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":true}
     *          ),
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
     * @throws
     */
    public function actionAdditionalEmailDelete()
    {
        $this->response = $this->classWebApi->additionalEmailDelete($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-create",
     *     tags={"Client/Employee"},
     *     summary="Создаем сотрудника в организации",
     *     description="Создаем сотрудника в организации",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "name": "Иван Иванович Иванов",
     *                      "email": "test@test.ru",
     *                      "phone": "+79271118899",
     *                      "role_id": 2
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default= {
     *                  "id": 1,
     *                  "name": "Иван Иванович Иванов",
     *                  "email": "test@test.ru",
     *                  "phone": "89271118899",
     *                  "role": "Менеджер",
     *                  "role_id": 2
     *              }
     *          ),
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
     * @throws
     */
    public function actionEmployeeCreate()
    {
        $this->response = $this->classWebApi->employeeAdd($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-get",
     *     tags={"Client/Employee"},
     *     summary="Получить сотрудника по id",
     *     description="Получить сотрудника по id",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"id": 1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default= {
     *                  "id": 1,
     *                  "name": "Иван Иванович Иванов",
     *                  "email": "test@test.ru",
     *                  "phone": "89271118899",
     *                  "role": "Менеджер",
     *                  "role_id": 2
     *              }
     *          ),
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
     * @throws
     */
    public function actionEmployeeGet()
    {
        $this->response = $this->classWebApi->employeeGet($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-update",
     *     tags={"Client/Employee"},
     *     summary="Обновление данных о сотруднике",
     *     description="Обновление данных о сотруднике",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "id": 1,
     *                      "name": "Иван Иванович Иванов",
     *                      "phone": "+79271118899",
     *                      "role_id": 2
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default= {
     *                  "id": 1,
     *                  "name": "Иван Иванович Иванов",
     *                  "email": "test@test.ru",
     *                  "phone": "+79271118899",
     *                  "role": "Менеджер",
     *                  "role_id": 1
     *              }
     *          ),
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
     * @throws
     */
    public function actionEmployeeUpdate()
    {
        $this->response = $this->classWebApi->employeeUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-delete",
     *     tags={"Client/Employee"},
     *     summary="Удаление сотрудника из организации",
     *     description="Удаление сотрудника из организации",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"id": 1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default= {"result":true}
     *          ),
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
     * @throws
     */
    public function actionEmployeeDelete()
    {
        $this->response = $this->classWebApi->employeeDelete($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-list",
     *     tags={"Client/Employee"},
     *     summary="Список сотрудников ресторана",
     *     description="Список сотрудников ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                               "search":"Имя, Email или Телефон",
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *    {
     *          "headers":{
     *              {
     *                  "id": "ID",
     *                  "name": "ФИО",
     *                  "email": "Email",
     *                  "phone": "Телефон",
     *                  "role": "Роль",
     *              }
     *          },
     *          "employees":
     *          {
     *              {
     *                  "id": 1,
     *                  "name": "Иван Иванович Иванов",
     *                  "email": "test@test.ru",
     *                  "phone": "89271118899",
     *                  "role": "Менеджер",
     *                  "role_id": 2
     *              }
     *          }
     *          ,
     *          "pagination":{
     *              "page":1,
     *              "total_page":1,
     *              "page_size":12
     *          }
     *     }
     *          ),
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
     * @throws
     */
    public function actionEmployeeList()
    {
        $this->response = $this->classWebApi->employeeList($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-search",
     *     tags={"Client/Employee"},
     *     summary="Поиск сотрудника по Email",
     *     description="Поиск сотрудника по Email",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"email":"test@test.ru"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default= {
     *                  "id": 1,
     *                  "name": "Иван Иванович Иванов",
     *                  "email": "test@test.ru",
     *                  "phone": "89271118899",
     *                  "role": "Менеджер",
     *                  "role_id": 2
     *              }
     *          ),
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
     * @throws
     */
    public function actionEmployeeSearch()
    {
        $this->response = $this->classWebApi->employeeSearch($this->request);
    }

    /**
     * @SWG\Post(path="/client/employee-roles",
     *     tags={"Client/Employee"},
     *     summary="Список ролей для сотрудников ресторана",
     *     description="Список ролей для сотрудников ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *              {
     *                 {
     *                      "role_id": 2,
     *                      "name": "Менеджер"
     *                 }
     *              }
     *          ),
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
     * @throws
     */
    public function actionEmployeeRoles()
    {
        $this->response = $this->classWebApi->employeeRoles();
    }

    /**
     * @SWG\Post(path="/client/get-license-mix-cart",
     *     tags={"Client"},
     *     summary="Информация о лицензии MixCart",
     *     description="Информация о лицензии MixCart",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "id": "11",
     *                  "name": "MC Light",
     *                  "is_active": "1",
     *                  "created_at": null,
     *                  "updated_at": null,
     *                  "login_allowed": "1",
     *                  "to_date": "2019-03-31T03:00:00+03:00",
     *                  "org_id": "1",
     *                  "manager_phone": "8 (499) 404-10-18"
     *              }
     *          ),
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
    public function actionGetLicenseMixCart()
    {
        $response = current(License::getMixCartLicenses($this->user->organization->id));
        $response['manager_phone'] = \Yii::$app->params['licenseManagerPhone'];
        $this->response = $response;
    }
}
