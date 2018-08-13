<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class VendorController
 * @package api_web\controllers
 */
class VendorController extends WebApiController
{
    /**
     * Отключение логирования
     * @var array
     */
    public $not_log_actions = [
        'upload-main-catalog'
    ];

    /**
     * @SWG\Post(path="/vendor/create",
     *     tags={"Vendor"},
     *     summary="Создание нового поставщика в системе, находясь в аккаунте ресторана",
     *     description="Создание нового поставщика в системе, находясь в аккаунте ресторана",
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
     *                               "user":{
     *                                   "vendor_id": 1,
     *                                   "email":"test@test.ru",
     *                                   "fio":"Donald Trump",
     *                                   "phone": "+79182225588",
     *                                   "organization_name": "ООО Рога и Копыта",
     *                                   "inn": "0001112223",
     *                                   "contact_name": "Контактное лицо"
     *                               },
     *                               "catalog":{
     *                                   "products":{
     *                                      {
     *                                          "product": "Треска горячего копчения",
     *                                          "price": 499.80,
     *                                          "ed": "шт."
     *                                      }
     *                                  },
     *                                  "currency_id": 1
     *                              }
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                "success": true,
     *                "organization_id": 2222,
     *                "user_id": 1111,
     *                "message": "Поставщик ООО Рога и Копыта и каталог добавлен! Инструкция по авторизации была отправлена на почту test@test.ru"
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
    public function actionCreate()
    {
        $this->response = $this->container->get('VendorWebApi')->create($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/search",
     *     tags={"Vendor"},
     *     summary="Поиск поставщика по email",
     *     description="Поиск поставщика по email",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"email":"test@test.ru"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/VendorSearch"
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
    public function actionSearch()
    {
        $this->response = $this->container->get('VendorWebApi')->search($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/update",
     *     tags={"Vendor"},
     *     summary="Редактирование поставщика",
     *     description="Редактирование поставщика",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="id - обязательное поле",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                             "id": 3551,
     *                             "name": "ООО Рога и Копыта",
     *                             "phone": "+79182225588",
     *                             "email":"test@test.ru",
     *                             "inn": "0001112223",
     *                             "contact_name": "Контактное имя",
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
     *              ref="#/definitions/Vendor"
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
    public function actionUpdate()
    {
        $this->response = $this->container->get('VendorWebApi')->update($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-logo",
     *     tags={"Vendor"},
     *     summary="Смена логотипа поставщика",
     *     description="Смена логотипа поставщика.",
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
     *                      "vendor_id": 1,
     *                      "image_source": "data:image/png;base64,iVBORw0KGgoAA=="
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              ref="#/definitions/Vendor"
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
    public function actionUploadLogo()
    {
        $this->response = $this->container->get('VendorWebApi')->uploadLogo($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-main-catalog",
     *     tags={"Vendor"},
     *     summary="Загрузка основного каталога",
     *     description="Загрузка основного каталога",
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
     *                      "cat_id": 4,
     *                      "data": "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,BASE64_ENCODE_SOURCE"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true,
     *                  "rows": {
     *                      {
     *                          "Артикул",
     *                          "Наименование",
     *                          "Кратность",
     *                          "Цена",
     *                          "Единица измерения",
     *                          "Комментарий"
     *                      },
     *                      {
     *                          "10",
     *                          "Товар 10",
     *                          "",
     *                          "100",
     *                          "бутылка",
     *                          ""
     *                      },
     *                      {
     *                          "111004",
     *                          "Балтика 7",
     *                          "1.25",
     *                          "45.5",
     *                          "бутылка",
     *                          ""
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
    public function actionUploadMainCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->uploadMainCatalog($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/get-list-main-index",
     *     tags={"Vendor"},
     *     summary="Список ключей для загрузки каталога",
     *     description="Список ключей для загрузки каталога",
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
    public function actionGetListMainIndex()
    {
        $this->response = $this->container->get('VendorWebApi')->getListMainIndex($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/import-main-catalog",
     *     tags={"Vendor"},
     *     summary="Маппинг, валидация и импорт основного каталога",
     *     description="Маппинг, валидация и импорт основного каталога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "cat_id": 4,
     *                      "uploaded_name": "dfg5fhbdhb",
     *                      "index_field": "ssid",
     *                      "mapping": {
     *                          "ssid": 1,
     *                          "article": 3,
     *                          "product_name": 2,
     *                          "unit": 4,
     *                          "price": 6,
     *                          "multiplicity": 7
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "cat_id": 4,
     *                  "uploaded_name": "dfg5fhbdhb"
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
    public function actionImportMainCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->importMainCatalog($this->request);
    }

    public function actionUploadCustomCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->uploadCustomCatalog($this->request);
    }

    public function actionImportCustomCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->importCustomCatalog($this->request);
    }

    public function actionDeleteMainCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->deleteMainCatalog($this->request);
    }

    public function actionChangeMainIndex()
    {
        $this->response = $this->container->get('VendorWebApi')->changeMainIndex($this->request);
    }

    public function actionDeleteTempMainCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->deleteTempMainCatalog($this->request);
    }

    public function actionGetTempMainCatalog()
    {
        $this->response = $this->container->get('VendorWebApi')->getTempMainCatalog($this->request);
    }
}