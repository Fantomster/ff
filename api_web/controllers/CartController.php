<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class CartController
 * @package api_web\controllers
 */
class CartController extends WebApiController
{
    /**
     * @SWG\Post(path="/cart/add",
     *     tags={"Cart"},
     *     summary="Добавить/Удалить товар в корзине",
     *     description="Добавляем или удаляем товар в корзине с помощью параметра quantity",
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
     *                  default={{"catalog_id":1, "product_id":1, "quantity":10}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
    {
    {
    "order":{
    "id":6548,
    "client_id":1,
    "vendor_id":3,
    "created_by_id":null,
    "accepted_by_id":null,
    "status":7,
    "total_price":"258.75",
    "created_at":"2018-02-06 13:21:18",
    "updated_at":"2018-02-06 13:23:22",
    "requested_delivery":null,
    "actual_delivery":null,
    "comment":"",
    "discount":"0.00",
    "discount_type":null,
    "currency_id":1,
    "status_text":"Формируется",
    "position_count":"1",
    "calculateDelivery":0
    },
    "organization":{
    "id":3,
    "type_id":2,
    "name":"bcpostavshik2@yandex.ru",
    "city":"Москва",
    "address":"Ломоносовчкий проспект 34 к 1",
    "zip_code":"232223",
    "phone":"+7 (926) 499 18 89",
    "email":"j262@mail.ru",
    "website":"ww.ru",
    "created_at":"2016-09-27 17:48:29",
    "updated_at":"2016-10-04 06:55:53",
    "step":0
    },
    "items":{
    {
    "id":18083,
    "order_id":6548,
    "product_id":1,
    "quantity":"1.000",
    "price":"258.75",
    "initial_quantity":null,
    "product_name":"Конфитюр Вишневый Люкс КТ80, ведро 14кг",
    "units":14,
    "article":"1",
    "comment":""
    }
    }
    }}
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
    public function actionAdd()
    {
        $this->response = $this->container->get('CartWebApi')->add($this->request);
    }

    /**
     * @SWG\Post(path="/cart/items",
     *     tags={"Cart"},
     *     summary="Список товаров в корзине",
     *     description="Получить список всех товаров в корзине",
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
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *                      {
     *                      {
     *                         "order":{
     *                             "id":6548,
     *                             "client_id":1,
     *                             "vendor_id":3,
     *                             "created_by_id":null,
     *                             "accepted_by_id":null,
     *                            "status":7,
     *                             "total_price":"258.75",
     *                             "created_at":"2018-02-06 13:21:18",
     *                             "updated_at":"2018-02-06 13:23:22",
     *                             "requested_delivery":null,
     *                             "actual_delivery":null,
     *                             "comment":"",
     *                             "discount":"0.00",
     *                             "discount_type":null,
     *                             "currency_id":1,
     *                             "status_text":"Формируется",
     *                             "position_count":"1",
     *                             "calculateDelivery":0
     *                         },
     *                         "organization":{
     *                             "id":3,
     *                             "type_id":2,
     *                             "name":"bcpostavshik2@yandex.ru",
     *                             "city":"Москва",
     *                             "address":"Ломоносовчкий проспект 34 к 1",
     *                             "zip_code":"232223",
     *                             "phone":"+7 (926) 499 18 89",
     *                             "email":"j262@mail.ru",
     *                             "website":"ww.ru",
     *                             "created_at":"2016-09-27 17:48:29",
     *                             "updated_at":"2016-10-04 06:55:53",
     *                             "step":0
     *                         },
     *                         "items":{
     *                             {
     *                                 "id":18083,
     *                                 "order_id":6548,
     *                                 "product_id":1,
     *                                 "quantity":"1.000",
     *                                 "price":"258.75",
     *                                 "initial_quantity":null,
     *                                 "product_name":"Конфитюр Вишневый Люкс КТ80, ведро 14кг",
     *                                 "units":14,
     *                                 "article":"1",
     *                                 "comment":""
     *                             }
     *                         }
     *                      }}
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
    public function actionItems()
    {
        $this->response = $this->container->get('CartWebApi')->items();
    }

    /**
     * @SWG\Post(path="/cart/clear",
     *     tags={"Cart"},
     *     summary="Полная очистка корзины",
     *     description="Полная очистка корзины",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description = "Если request пустой, удаляются все заказы",
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"order_id":"1"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
    {
     *
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
    public function actionClear()
    {
        $this->response = $this->container->get('CartWebApi')->clear($this->request);
    }
}