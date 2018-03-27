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
    {
    "product": "Треска горячего копчения",
    "price": 499.80,
    "ed": "шт."
    }
     *                               },
     *     "currency_id": 1
     *                           }
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
                                "success": true,
     *                          "organization_id": 2222,
     *                          "user_id": 1111,
                                "message": "Поставщик ООО Рога и Копыта и каталог добавлен! Инструкция по авторизации была отправлена на почту test@test.ru"
    }
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
     *                         "cat_id": 0,
     *                         "email": "testsellfknm4@yandex.ru",
     *                         "phone": "+7 925 764-84-45",
     *                         "status": "Партнер. Каталог не назначен",
     *                         "picture": "https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca",
     *                         "address": "Россия, Москва, Волгоградский проспект",
     *                         "rating": 31
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
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                             "id": 3551,
     *                             "email":"test@test.ru",
     *                             "fio":"Donald Trump",
     *                             "phone": "+79182225588",
     *                             "organization_name": "ООО Рога и Копыта",
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "id": 3551,
     *                  "name": "PIXAR STUDIO",
     *                  "cat_id": 1,
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.gif",
     *                  "address": "Ханты-Мансийск, улица Ленина",
     *                  "status":"Партнер"
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
    public function actionUpdate()
    {
        $this->response = $this->container->get('VendorWebApi')->update($this->request);
    }
}