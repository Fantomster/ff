<?php

namespace api_web\controllers;

use api_web\components\Notice;
use api_web\components\WebApiController;

/**
 * Class UserController
 * @package api\modules\v1\modules\web\controllers
 */
class UserController extends WebApiController
{
    /**
     * @SWG\Post(path="/user/registration",
     *     tags={"User"},
     *     summary="Регистрация пользователя",
     *     description="Создание нового пользователя, отправка СМС с кодом подтверждения",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"user": {"email": "neo@neo.com","password": "new"},"profile": {"phone": "+79182225588"},"organization": {"type_id": 1}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"user_id":1}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionRegistration()
    {
        $this->response = [
            'user_id' => $this->container->get('UserWebApi')->create($this->request)
        ];
    }

    /**
     * @SWG\Post(path="/user/registration-confirm",
     *     tags={"User"},
     *     summary="Подтверждение регистрации",
     *     description="Активирует пользователя, позволяя войти в систему.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"user_id":1, "code":3344}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"token":"111222333444"}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionRegistrationConfirm()
    {
        $this->response = [
            'token' => $this->container->get('UserWebApi')->confirm($this->request)
        ];
    }

    /**
     * @SWG\Post(path="/user/login",
     *     tags={"User"},
     *     summary="Авторизация пользователя",
     *     description="Метод позволяет получить токен пользователя для дальнейшего взаимодействия с API",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"email":"neo@neo.com", "password":"neo"}
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *{
     *       "token": "asdasd123123"
     *}
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionLogin()
    {
        $this->response = ['token' => $this->user->access_token];
    }

    /**
     * @SWG\Post(path="/user/organization",
     *     tags={"User"},
     *     summary="Список доступных организаций пользователя",
     *     description="Получить список всех организаций пользователя",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/UserWebApiDefinition"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *   {
     *         "organization": {
     *           {
     *               "id": 1,
     *               "type_id": 2,
     *               "name": "ООО Рога и Копыта",
     *               "city": "Москва",
     *               "address": "ул. Госпитальный Вал, Москва, Россия",
     *               "phone": "+79162225588",
     *               "email": "test@test.ru",
     *               "picture": "http://mixcart.ru/pic/pic1.jpeg",
     *               "rating": 23
     *           }
     *         }
     *   }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionOrganization()
    {
        $this->response = $this->container->get('UserWebApi')->getAllOrganization();
    }

    /**
     * @SWG\Post(path="/user/set-organization",
     *     tags={"User"},
     *     summary="Переключение текщей организации пользователя",
     *     description="Переключение текщей организации пользователя",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/UserWebApiDefinition"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"organization_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"result":1}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionSetOrganization()
    {
        $this->response = [
            'result' => $this->container->get('UserWebApi')->setOrganization($this->request)
        ];
    }

    /**
     * @SWG\Post(path="/user/password-recovery",
     *     tags={"User"},
     *     summary="Восстановление пароля",
     *     description="Отправить письмо на email с сылкой на восстановление пароля",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"email":"test@test.ru"}
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result": 1}
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     )
     * )
     */
    public function actionPasswordRecovery()
    {
        $this->response = [
            'result' => Notice::init('User')->sendEmailRecoveryPassword($this->request['email'])
        ];
    }

    /**
     * @SWG\Post(path="/user/vendor-status-list",
     *     tags={"User"},
     *     summary="Список статусов поставщиков",
     *     description="Список статусов поставщиков для фильтра",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={1:"Партнер"}
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
    public function actionVendorStatusList()
    {
        $this->response = $this->container->get('UserWebApi')->getVendorStatusList();
    }

    /**
     * @SWG\Post(path="/user/vendors",
     *     tags={"User"},
     *     summary="Список поставщиков",
     *     description="Получить список поставщиков пользователя",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "address":"Москва",
     *                                   "status":1
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-name"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                               "headers":{
     *                                   {
     *                                       "name": "Название",
     *                                       "location": "Расположение"
     *                                   }
     *                               }
     *                               ,
     *                               "vendors":{
     *                               {
     *                                       "id": 3551,
     *                                       "name": "PIXAR STUDIO",
     *                                       "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.gif",
     *                                       "location": "Ханты-Мансийск, улица Ленина",
     *                                       "status":"Партнер"
     *                               }}
     *                               ,
     *                               "pagination":{
     *                                   "page":1,
     *                                   "total_page":2,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-name"
     *                      }
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
    public function actionVendors()
    {
        $this->response = $this->container->get('UserWebApi')->getVendors($this->request);
    }

    /**
     * @SWG\Post(path="/user/remove-vendor",
     *     tags={"User"},
     *     summary="Открепить поставщика",
     *     description="Удаляем связь между рестораном и поставщиком",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "vendor_id":1
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result": true}
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
    public function actionRemoveVendor()
    {
        $this->response = $this->container->get('UserWebApi')->removeVendor($this->request);
    }
}
