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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
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
     *              ref="#/definitions/Organization"
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
}