<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:34
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class PreorderController
 *
 * @property \api_web\classes\PreorderWebApi $classWebApi
 * @package api_web\controllers
 */
class PreorderController extends WebApiController
{
    public $className = \api_web\classes\PreorderWebApi::class;

    /**
     * @SWG\Post(path="/preorder/list",
     *     tags={"Preorder"},
     *     summary="Список предзаказов",
     *     description="Список предзаказов",
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
     *                      "search": {
     *                          "query": "по полям ID или Кем создан заказ",
     *                          "date": {
     *                              "from": "dd.mm.yyyy",
     *                              "to": "dd.mm.yyyy"
     *                          },
     *                          "price": {
     *                              "from": 0,
     *                              "to": 250000
     *                          },
     *                          "status": 1
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "-id"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"items":{{
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "created_at": "2017-10-06T23:00:00+03:00",
     *                          "upated_at": "2017-10-06T23:00:00+03:00"
     *              }}}
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
    public function actionList()
    {
        $this->response = $this->classWebApi->list($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/create",
     *     tags={"Preorder"},
     *     summary="Создание предзаказа",
     *     description="Создание предзаказа",
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
     *                      "vendor_id": 50
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "created_at": "2017-10-06T23:00:00+03:00",
     *                          "upated_at": "2017-10-06T23:00:00+03:00"
     *              }
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
    public function actionCreate()
    {
        $this->response = $this->classWebApi->create($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/complete",
     *     tags={"Preorder"},
     *     summary="Завершение предзаказа",
     *     description="Завершение предзаказа",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "created_at": "2017-10-06T23:00:00+03:00",
     *                          "upated_at": "2017-10-06T23:00:00+03:00"
     *              }
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
    public function actionComplete()
    {
        $this->response = $this->classWebApi->complete($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/get-orders",
     *     tags={"Preorder"},
     *     summary="Список заказов в предзаказе",
     *     description="Список заказов в предзаказе",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={"items":{{
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image":
     *                      "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }}}
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
    public function actionGetOrders()
    {
        $this->response = $this->classWebApi->orders($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/get",
     *     tags={"Preorder"},
     *     summary="Детальная информация предзаказа",
     *     description="Детальная информация предзаказа",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "products": {
     *                          {
     *                             "id": 1,
     *                             "name": "Колбаса сушенная горная",
     *                             "article": "БС-1-200 VM",
     *                             "plan_quantity": 10,
     *                             "quantity": 17,
     *                             "sum": "611,66",
     *                             "isset_analog": false
     *                          },
     *                          {
     *                             "id": 2,
     *                             "name": "Колбаса вареная",
     *                             "article": "KVM",
     *                             "plan_quantity": 10,
     *                             "quantity": 13,
     *                             "sum": "811,66",
     *                             "isset_analog": false
     *                          }
     *                          },
     *                          "created_at": "2019-02-05T23:00:00+03:00",
     *                          "upated_at": "2019-02-05T23:00:00+03:00"
     *              }
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
    public function actionGet()
    {
        $this->response = $this->classWebApi->get($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/confirm-orders",
     *     tags={"Preorder"},
     *     summary="Оформить все заказы",
     *     description="Метод переводит все заказы в этом предзаказе в `статус ожидает подтверждения поставщика`",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={"items":{{
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image":
     *                      "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }}}
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
    public function actionConfirmOrders()
    {
        $this->response = $this->classWebApi->confirmOrders($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/confirm-order",
     *     tags={"Preorder"},
     *     summary="Оформить заказ",
     *     description="Метод переводит заказ в этом предзаказе в `статус ожидает подтверждения поставщика`",
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
     *                      "order_id": 2
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image":
     *                      "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *         }
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
    public function actionConfirmOrder()
    {
        $this->response = $this->classWebApi->confirmOrder($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/add-product",
     *     tags={"Preorder"},
     *     summary="Добавить продукт в предзаказ",
     *     description="Метод добавляет продукт(ы) в предзаказ",
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
     *                       "id": 1,
     *                       "products": {
     *                           {
     *                               "id": 467832,
     *                               "cat_id": 3454,
     *                               "vendor_id": 12,
     *                               "quantity": 10
     *                           },
     *                           {
     *                               "id": 467831,
     *                               "cat_id": 3451,
     *                               "vendor_id": 1,
     *                               "quantity": 4
     *                           }
     *                       }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "products": {
     *                          {
     *                             "id": 1,
     *                             "name": "Колбаса сушенная горная",
     *                             "article": "БС-1-200 VM",
     *                             "plan_quantity": 10,
     *                             "quantity": 17,
     *                             "sum": "611,66",
     *                             "isset_analog": false
     *                          },
     *                          {
     *                             "id": 2,
     *                             "name": "Колбаса вареная",
     *                             "article": "KVM",
     *                             "plan_quantity": 10,
     *                             "quantity": 13,
     *                             "sum": "811,66",
     *                             "isset_analog": false
     *                          }
     *                          },
     *                          "created_at": "2019-02-05T23:00:00+03:00",
     *                          "upated_at": "2019-02-05T23:00:00+03:00"
     *              }
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
    public function actionAddProduct()
    {
        $this->response = $this->classWebApi->addProduct($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/update-product",
     *     tags={"Preorder"},
     *     summary="Обновление количества в заказе предзаказа",
     *     description="Обновление количества в заказе предзаказа",
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
     *                      "id":1,
     *                      "quantity": 6
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={}
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
    public function actionUpdateProduct()
    {
        $this->response = $this->classWebApi->updateProduct($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/update-order",
     *     tags={"Preorder"},
     *     summary="Обновление даты доставки, и комментария к заказу",
     *     description="Обновление даты доставки, и комментария к заказу",
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
     *                      "order_id":1,
     *                      "requested_delivery": "d.m.Y",
     *                      "comment":"text"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={}
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
    public function actionUpdateOrder()
    {
        $this->response = $this->classWebApi->updateOrder($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/order-clear",
     *     tags={"Preorder"},
     *     summary="Очистить заказ",
     *     description="Очистить заказ",
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
     *                      "order_id":1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={}
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
    public function actionOrderClear()
    {
        $this->response = $this->classWebApi->orderClear($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/order-repeat",
     *     tags={"Preorder"},
     *     summary="Повторить заказ",
     *     description="Повторить заказ",
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
     *                      "order_id":1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={}
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
    public function actionOrderRepeat()
    {
        $this->response = $this->classWebApi->orderRepeat($this->request);
    }

    /**
     * @SWG\Post(path="/preorder/order-cancel",
     *     tags={"Preorder"},
     *     summary="Отменить заказ",
     *     description="Отменить заказ",
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
     *                      "order_id":1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={}
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
    public function actionOrderCancel()
    {
        $this->response = $this->classWebApi->orderCancel($this->request);
    }
}
