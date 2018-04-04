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
     * @SWG\Post(path="/client/employee-create",
     *     tags={"Client"},
     *     summary="Создаем сотрудника в организации",
     *     description="Создаем сотрудника в организации",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "name": "Иван Иванович Иванов",
     *                      "email": "test@test.ru",
     *                      "phone": "89271118899",
     *                      "role": "Менеджер"
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
     *     tags={"Client"},
     *     summary="Получить сотрудника по id",
     *     description="Получить сотрудника по id",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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
     *     tags={"Client"},
     *     summary="Обновление данных о сотруднике",
     *     description="Обновление данных о сотруднике",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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
     *     tags={"Client"},
     *     summary="Удаление сотрудника из организации",
     *     description="Удаление сотрудника из организации",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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
     *     tags={"Client"},
     *     summary="Список сотрудников ресторана",
     *     description="Список сотрудников ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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
     *     tags={"Client"},
     *     summary="Поиск сотрудника по Email",
     *     description="Поиск сотрудника по Email",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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
     *     tags={"Client"},
     *     summary="Список ролей для сотрудников ресторана",
     *     description="Список ролей для сотрудников ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
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