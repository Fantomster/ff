<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class ClientController
 * @package api_web\controllers
 */
class ClientController extends WebApiController
{
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
     *                          "image": "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
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
     */
    public function actionDetail()
    {
        $this->response = $this->container->get('ClientWebApi')->detail();
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
     *                          "image": "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
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
     */
    public function actionDetailUpdate()
    {
        $this->response = $this->container->get('ClientWebApi')->detailUpdate($this->request);
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
     *                          "image": "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
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
     */
    public function actionDetailUpdateLogo()
    {
        $this->response = $this->container->get('ClientWebApi')->detailUpdateLogo($this->request);
    }

    /**
     * @SWG\Post(path="/client/additional-email-create",
     *     tags={"Client/AdditionalEmail"},
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
     */
    public function actionAdditionalEmailCreate()
    {
        $this->response = $this->container->get('ClientWebApi')->additionalEmailCreate($this->request);
    }

    /**
     * @SWG\Post(path="/client/additional-email-list",
     *     tags={"Client/AdditionalEmail"},
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
     *                      "id": 2,
     *                      "email": "email@email.ru",
     *                      "organization_id": 1,
     *                      "order_created": 1,
     *                      "order_canceled": 1,
     *                      "order_changed": 1,
     *                      "order_processing": 0,
     *                      "order_done": 1,
     *                      "request_accept": 0
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
     */
    public function actionAdditionalEmailList()
    {
        $this->response = $this->container->get('ClientWebApi')->additionalEmailList();
    }

    /**
     * @SWG\Post(path="/client/additional-email-update",
     *     tags={"Client/AdditionalEmail"},
     *     summary="Обновить дополнительный email адрес для уведомлений",
     *     description="Обновить дополнительный email адрес для уведомлений",
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
     *                      "id": 2,
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
     */
    public function actionAdditionalEmailUpdate()
    {
        $this->response = $this->container->get('ClientWebApi')->additionalEmailUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/client/additional-email-delete",
     *     tags={"Client/AdditionalEmail"},
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
     */
    public function actionAdditionalEmailDelete()
    {
        $this->response = $this->container->get('ClientWebApi')->additionalEmailDelete($this->request);
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
     *                      "phone": "89271118899",
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
     */
    public function actionEmployeeCreate()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeAdd($this->request);
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
     */
    public function actionEmployeeGet()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeGet($this->request);
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
     */
    public function actionEmployeeUpdate()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeUpdate($this->request);
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
     *              default= {}
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
    public function actionEmployeeDelete()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeDelete($this->request);
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
     */
    public function actionEmployeeList()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeList($this->request);
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
     */
    public function actionEmployeeSearch()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeSearch($this->request);
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
     */
    public function actionEmployeeRoles()
    {
        $this->response = $this->container->get('ClientWebApi')->employeeRoles($this->request);
    }
}