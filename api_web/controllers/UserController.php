<?php

namespace api_web\controllers;

use api_web\classes\UserWebApi;
use api_web\components\Notice;
use api_web\components\Registry;
use api_web\components\WebApiController;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

/**
 * Class UserController
 *
 * @property UserWebApi $classWebApi
 * @package api\modules\v1\modules\web\controllers
 */
class UserController extends WebApiController
{
    public $className = UserWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'get',
                        'vendors',
                    ],
                    'roles'      => [Registry::OPERATOR],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'remove-vendor',
                    ],
                    'roles'      => [Registry::PURCHASER_RESTAURANT],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'get-gmt',
                        'registration',
                        'registration-repeat-sms',
                        'registration-confirm',
                        'login',
                        'password-recovery',
                        'vendor-status-list',
                        'vendor-location-list',
                        'password-change',
                        'mobile-change',
                        'get-agreement',
                        'change-unconfirmedUsers-phone',
                        'get-available-businesses',
                        'set-agreement',
                    ],
                    'roles'      => [Registry::OPERATOR],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'   => true,
                    'actions' => [
                        'organization',
                        'set-organization',
                    ],
                    'roles'   => [Registry::AUTH_USER]
                ]
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

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
     * @throws \Exception
     */
    public function actionGet()
    {
        $this->response = $this->classWebApi->get($this->request);
    }

    /**
     * @SWG\Post(path="/user/get-gmt",
     *     tags={"User"},
     *     summary="Часовой пояс пользователя",
     *     description="Часовой пояс пользователя, из заголовка GTM",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          name="GMT",
     *          in="header",
     *          required=false,
     *          type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                   "GMT": 3
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
     * @throws
     */
    public function actionGetGmt()
    {
        $this->response = $this->classWebApi->getGmt();
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
     *                  default={"user": {"email": "neo@neo.com","password": "new"},"profile": {"phone":
     *                  "+79182225588"},"organization": {"type_id": 1}}
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
     * @throws
     */
    public function actionRegistration()
    {
        $this->response = [
            'user_id' => $this->classWebApi->create($this->request)
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
     * @throws
     */
    public function actionRegistrationRepeatSms()
    {
        $this->response = $this->classWebApi->registrationRepeatSms($this->request);
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
     * @throws
     */
    public function actionRegistrationConfirm()
    {
        $this->response = [
            'token' => $this->classWebApi->confirm($this->request)
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
     * @throws
     */
    public function actionLogin()
    {
        $this->response = ['token' => $this->user->getJWTToken()];
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
     * @throws
     */
    public function actionOrganization()
    {
        $this->response = $this->classWebApi->getAllOrganization();
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
     *            default={"result":1, "jwt_token":"jwt_token"}
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
     * @throws
     */
    public function actionSetOrganization()
    {
        $this->response = [
            'result'    => $this->classWebApi->setOrganization($this->request),
            'jwt_token' => $this->user->getJWTToken(\Yii::$app->jwt),
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
     * @throws
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
        $this->response = $this->classWebApi->getVendorStatusList();
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
        $this->response = $this->classWebApi->getVendorLocationList();
    }

    /**
     * @SWG\Post(path="/user/vendors",
     *     tags={"User"},
     *     summary="Список поставщиков",
     *     description="Получить список поставщиков пользователя
     *     enum_status: {partner, catalog_not_set, send_invite}",
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
     *                                       "picture":
     *                                       "https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca",
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
     * @throws \Exception
     */
    public function actionVendors()
    {
        $this->response = $this->classWebApi->getVendors($this->request);
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
     * @throws
     */
    public function actionRemoveVendor()
    {
        $this->response = $this->classWebApi->removeVendor($this->request);
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
     * @throws
     */
    public function actionPasswordChange()
    {
        $this->response = $this->classWebApi->changePassword($this->request);
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
     * @throws
     */
    public function actionMobileChange()
    {
        $this->response = $this->classWebApi->mobileChange($this->request);
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
     * @throws
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

    /**
     * @SWG\Post(path="/user/change-unconfirmed-users-phone",
     *     tags={"User"},
     *     summary="Смена телефона неподтвержденным пользователем",
     *     description="Смена телефона неподтвержденным пользователем.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"user": {"id": 1},
     *                      "phone": "+79182225587",
     *                      "code": 4433
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"result":true}
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
     * @throws
     */
    public function actionChangeUnconfirmedUsersPhone()
    {
        $this->response = $this->classWebApi->mobileChange($this->request, true);
    }

    /**
     * @SWG\Post(path="/user/get-available-businesses",
     *     tags={"User"},
     *     summary="Список фильтров имен бизнесов",
     *     description="Список доступных бизнесов для текущего юзера",
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
     *
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                      {
     *                          "id": "4300",
     *                          "name": "test346346",
     *                          "license_is_active": true
     *                      },
     *                      {
     *                          "id": "4300",
     *                          "name": "test346346",
     *                          "license_is_active": true
     *                      },
     *                      {
     *                          "id": "4300",
     *                          "name": "test346346",
     *                          "license_is_active": false
     *                      }
     *                  }
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
     * @throws \Exception
     */
    public function actionGetAvailableBusinesses()
    {
        $this->response = $this->classWebApi->getUserOrganizationBusinessList();
    }

    /**
     * @SWG\Post(path="/user/set-agreement",
     *     tags={"User"},
     *     summary="Метод сохранения принятия/отказа от соглашений",
     *     description="Доступные типы [type] соглашений: user_agreement | confidencial_policy",
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
     *                      "type": "user_agreement",
     *                      "value":1
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                      "id": 3768,
     *                      "type_id": 1,
     *                      "name": "капотник",
     *                      "city": "Омская область",
     *                      "address": "Россия, Омск, Омская область, улица Гагарина, 14",
     *                      "zip_code": "",
     *                      "phone": "+7 977 879-77-83",
     *                      "email": "yemail@yemail.ru",
     *                      "website": "www.dmitov.com",
     *                      "created_at": "2017-09-27 08:57:32",
     *                      "updated_at": "2018-11-09 07:49:06",
     *                      "step": 0,
     *                      "legal_entity": "Legal Entity",
     *                      "contact_name": "John Doe",
     *                      "about": "CV",
     *                      "picture": "5ac77834ceb67.jpg",
     *                      "es_status": 1,
     *                      "rating": 0,
     *                      "white_list": 0,
     *                      "partnership": 0,
     *                      "lat": 54.9852,
     *                      "lng": 73.3795,
     *                      "country": "Россия",
     *                      "locality": "Омская область",
     *                      "route": "улица Гагарина",
     *                      "street_number": "14",
     *                      "place_id": "ChIJVVVFARD-qkMRqJukZRInTYU",
     *                      "formatted_address": "Россия, Омск, Омская область, улица Гагарина, 14",
     *                      "administrative_area_level_1": "Омск",
     *                      "franchisee_sorted": 1,
     *                      "blacklisted": 0,
     *                      "parent_id": 4398,
     *                      "manager_id": null,
     *                      "is_allowed_for_franchisee": 1,
     *                      "is_work": null,
     *                      "inn": null,
     *                      "kpp": null,
     *                      "gmt": 3,
     *                      "lang": "ru",
     *                      "user_agreement": 1,
     *                      "confidencial_policy": 1
     *                  }
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
     * @throws \Exception
     */
    public function actionSetAgreement()
    {
        $this->response = $this->classWebApi->setAgreement($this->request);
    }

}
