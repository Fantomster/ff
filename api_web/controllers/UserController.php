<?php

namespace api_web\controllers;

use api_web\components\Notice;
use api_web\components\WebApiController;
use yii\web\BadRequestHttpException;

/**
 * Class UserController
 * @package api\modules\v1\modules\web\controllers
 */
class UserController extends WebApiController
{
    /**
     * @SWG\Post(path="/user/get",
     *     tags={"User"},
     *     summary="Информация о пользователе",
     *     description="Информация о пользователе",
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
     *                  default={"id":1, "email":"neo@neo.com"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                   "id": 5,
     *                   "email": "mail@yandex.ru",
     *                   "phone": "+79999999999",
     *                   "name": "Годный Старец",
     *                   "role_id": 3,
     *                   "role": "Руководитель"
     *               }
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
    public function actionGet()
    {
        $this->response = $this->container->get('UserWebApi')->get($this->request);
    }

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
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
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
     * @SWG\Post(path="/user/registration-repeat-sms",
     *     tags={"User"},
     *     summary="Повторная отправка СМС",
     *     description="Повторная отправка СМС",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"user_id":1}
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
    public function actionRegistrationRepeatSms()
    {
        $this->response = $this->container->get('UserWebApi')->registrationRepeatSms($this->request);
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
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
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
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
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
     *               "name": "El postavshik",
     *               "phone": "",
     *               "email": "El-postavshik@El1postavshik.ru",
     *               "address": "ул. Егорьевская, 1, Москва, Россия, 109387",
     *               "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *               "type_id": 2,
     *               "type": "Поставщик",
     *               "rating": 0,
     *               "city": "Москва",
     *               "administrative_area_level_1": null,
     *               "country": "Россия",
     *               "about": ""
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
     *     summary="Переключение текущей организации пользователя",
     *     description="Переключение текущей организации пользователя",
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
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
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
     * @SWG\Post(path="/user/vendor-location-list",
     *     tags={"User"},
     *     summary="Список географического расположения поставщиков",
     *     description="Список географического расположения поставщиков для фильтра",
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
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  {"title":"Россия, г. Москва", "value":"Россия:Москва"},
     *                  {"title":"Россия, г. Казань", "value":"Россия:Казань"}
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
    public function actionVendorLocationList()
    {
        $this->response = $this->container->get('UserWebApi')->getVendorLocationList();
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "location":"Россия:Москва",
     *                                   "name":"поставщик",
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
     *                                       "id": "ID",
     *                                       "name": "Название организации",
     *                                       "cat_id": "Каталог",
     *                                       "email": "Email организации",
     *                                       "phone": "Телефон",
     *                                       "status": "Status",
     *                                       "picture": "Аватар",
     *                                       "address": "Адрес",
     *                                       "rating": "Rating",
     *                                       "allow_editing": "Allow Editing"
     *                                   }
     *                               }
     *                               ,
     *                               "vendors":{
     *                               {
     *                                       "id": 3449,
     *                                       "name": "testsellfknm4 - поставщик",
     *                                       "contact_name": "Имя контакта",
     *                                       "inn": "00011133",
     *                                       "cat_id": 0,
     *                                       "email": "testsellfknm4@yandex.ru",
     *                                       "phone": "+7 925 764-84-45",
     *                                       "status": "Партнер. Каталог не назначен",
     *                                       "picture": "https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca",
     *                                       "address": "Россия, Москва, Волгоградский проспект",
     *                                       "rating": 31,
     *                                       "allow_editing": 1
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "vendor_id":1
     *                  }
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

    /**
     * @SWG\Post(path="/user/password-change",
     *     tags={"User"},
     *     summary="Смена пароля пользователя",
     *     description="Смена пароля пользователя",
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
     *                      "password": "qazwsx",
     *                      "new_password": "qazwsx123",
     *                      "new_password_confirm": "qazwsx123",
     *                  }
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
    public function actionPasswordChange()
    {
        $this->response = $this->container->get('UserWebApi')->changePassword($this->request);
    }

    /**
     * @SWG\Post(path="/user/mobile-change",
     *     tags={"User"},
     *     summary="Смена телефона пользователя",
     *     description="Смена телефона пользователя.
     *     Запрос на отправку смс ,или повторную отправку, осуществяется без параметра code
     *     Параметр code указываем только для проверки кода.",
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
     *                      "phone": "+79162221133",
     *                      "code": 4433
     *                  }
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
    public function actionMobileChange()
    {
        $this->response = $this->container->get('UserWebApi')->mobileChange($this->request);
    }

    /**
     * @SWG\Post(path="/user/get-agreement",
     *     tags={"User"},
     *     summary="Пользовательское соглашение",
     *     description="Пользовательское соглашение (UserAgreement, ConfidencialPolicy)",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"type":"UserAgreement"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                   "text": "Текст соглашения"
     *               }
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
    public function actionGetAgreement()
    {
        if (empty($this->request['type'])) {
            throw new BadRequestHttpException('empty_param|type');
        }
        if (!in_array($this->request['type'], ['UserAgreement', 'ConfidencialPolicy'])) {
            throw new BadRequestHttpException('page_not_found');
        }

        $this->response['text'] = \Yii::t('api_web', 'api_web.user.agreement.' . $this->request['type']);
    }
}
