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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "user":{
     *                                   "email":"test@test.ru",
     *                                   "fio":"Donald Trump",
     *                                   "phone": "+79182225588",
     *                                   "organization_name": "ООО Рога и Копыта",
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              default={
     *                         "id": 3449,
     *                         "name": "testsellfknm4 - поставщик",
     *                         "phone": "+7 925 764-84-45",
     *                         "email": "testsellfknm4@yandex.ru",
     *                         "address": "Волгоградский пр., 143к2, Москва, Россия, 109378",
     *                         "image": "https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca",
     *                         "type_id": 2,
     *                         "type": "Поставщик",
     *                         "rating": 5,
     *                         "city": "Москва",
     *                         "administrative_area_level_1": null,
     *                         "country": "Россия",
     *                         "about": "1233"
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                             "id": 3551,
     *                             "name": "ООО Рога и Копыта",
     *                             "phone": "+79182225588",
     *                             "email":"test@test.ru",
     *                             "site": "www.mixcart.ru",
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
     *                         "id": 3948,
     *                         "name": "ООО Рога и Копыта",
     *                         "phone": "+79182225588",
     *                         "email": "cn13@cn13.ru",
     *                         "site": "www.mixcart.ru",
     *                         "address": "Россия, Московская область, Люберцы, улица Побратимов, 7",
     *                         "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                         "type_id": 2,
     *                         "type": "Поставщик",
     *                         "rating": 0,
     *                         "city": "Люберцы",
     *                         "administrative_area_level_1": "Московская область",
     *                         "country": "Россия",
     *                         "about": "",
     *                         "allow_editing": 1
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
    public function actionUpdate()
    {
        $this->response = $this->container->get('VendorWebApi')->update($this->request);
    }

    /**
     * @SWG\Post(path="/vendor/upload-logo",
     *     tags={"Vendor"},
     *     security={
     *         {
     *             "Bearer": {}
     *         }
     *     },
     *     consumes={"multipart/form-data"},
     *     summary="Смена логотипа поставщика",
     *     description="Смена логотипа поставщика.
     * Необходима авторизация по Header:Bearer xxxxxxxxx",
     *     consumes={"multipart/form-data"},
     *     @SWG\Parameter(
     *         in="formData",
     *         name="Organization[picture]",
     *         required=true,
     *         type="file"
     *     ),
     *     @SWG\Parameter(
     *         name="vendor_id",
     *         in="formData",
     *         required=true,
     *         format="int32",
     *         type="integer"
     *     ),
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                         "id": 3948,
     *                         "name": "ООО Рога и Копыта",
     *                         "phone": "+79182225588",
     *                         "email": "cn13@cn13.ru",
     *                         "site": "www.mixcart.ru",
     *                         "address": "Россия, Московская область, Люберцы, улица Побратимов, 7",
     *                         "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                         "type_id": 2,
     *                         "type": "Поставщик",
     *                         "rating": 0,
     *                         "city": "Люберцы",
     *                         "administrative_area_level_1": "Московская область",
     *                         "country": "Россия",
     *                         "about": "",
     *                         "allow_editing": 1
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
     *                      "data": "base64shit"
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
     *                  "uploaded_name": "dfg5fhbdhb",
     *                  "sample_rows: {
     *                      "1" : {
     *                          "1": "data-1-1",
     *                          "2": "data-1-2",
     *                          "3": "data-1-3",
     *                      },
     *                      "2" : {
     *                          "1": "data-2-1",
     *                          "2": "data-2-2",
     *                          "3": "data-2-3",
     *                      },
     *                      "3" : {
     *                          "1": "data-3-1",
     *                          "2": "data-3-2",
     *                          "3": "data-3-3",
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
    public function actionUploadMainCatalog() {
        $this->response = $this->container->get('VendorWebApi')->uploadMainCatalog($this->request);
    }
    
    /**
     * @SWG\Post(path="/vendor/upload-main-catalog",
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
    public function actionImportMainCatalog() {
        $this->response = $this->container->get('VendorWebApi')->importMainCatalog($this->request);
    }
    
    public function actionUploadCustomCatalog() {
        $this->response = $this->container->get('VendorWebApi')->uploadCustomCatalog($this->request);
    }
    
    public function actionImportCustomCatalog() {
        $this->response = $this->container->get('VendorWebApi')->importCustomCatalog($this->request);
    }
}