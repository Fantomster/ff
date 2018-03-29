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
     *              {
     *                  {
     *                      "order":{
     *                          "id":6548,
     *                          "client_id":1,
     *                          "vendor_id":3,
     *                          "created_by_id":null,
     *                          "accepted_by_id":null,
     *                          "status":7,
     *                          "total_price": 258.75,
     *                          "created_at":"2018-02-06 13:21:18",
     *                          "updated_at":"2018-02-06 13:23:22",
     *                          "requested_delivery":null,
     *                          "actual_delivery":null,
     *                          "comment":"",
     *                          "discount": 0.00,
     *                          "discount_type":null,
     *                          "currency_id":1,
     *                          "min_order_price": 0,
     *                          "delivery_price": 0,
     *                          "status_text":"Формируется",
     *                          "position_count":1,
     *                          "calculateDelivery":0
     *                     },
     *                     "organization":{
     *                          "id": 3948,
     *                          "name": "ООО Рога и Копыта",
     *                          "phone": "+79182225588",
     *                          "email": "test@test.ru",
     *                          "site": "www.mixcart.ru",
     *                          "address": "Россия, Московская область, Люберцы, улица Побратимов, владение 107",
     *                          "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                          "type_id": 2,
     *                          "type": "Поставщик",
     *                          "rating": 0,
     *                          "city": "Люберцы",
     *                          "administrative_area_level_1": "Московская область",
     *                          "country": "Россия",
     *                          "about": "",
     *                          "allow_editing": 1
     *                     },
     *                     "items":{
     *                          {
     *                              "id": 481057,
     *                              "product": "Тестовый товар, сам добавил",
     *                              "catalog_id": 3022,
     *                              "category_id": 0,
     *                              "price": 100,
     *                              "rating": 0,
     *                              "supplier": "ООО Рога и Копыта",
     *                              "brand": "",
     *                              "article": "1",
     *                              "ed": "шт",
     *                              "units": 0,
     *                              "currency": "RUB",
     *                              "image": "data:image/png;base64,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",
     *                              "in_basket": 0.25
     *                          }
     *                     }
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
     *              {
     *                  {
     *                      "order":{
     *                          "id":6548,
     *                          "client_id":1,
     *                          "vendor_id":3,
     *                          "created_by_id":null,
     *                          "accepted_by_id":null,
     *                          "status":7,
     *                          "total_price": 258.75,
     *                          "created_at":"2018-02-06 13:21:18",
     *                          "updated_at":"2018-02-06 13:23:22",
     *                          "requested_delivery":null,
     *                          "actual_delivery":null,
     *                          "comment":"",
     *                          "discount": 0.00,
     *                          "discount_type":null,
     *                          "currency_id":1,
     *                          "min_order_price": 0,
     *                          "delivery_price": 0,
     *                          "status_text":"Формируется",
     *                          "position_count":1,
     *                          "calculateDelivery":0
     *                     },
     *                     "organization":{
     *                          "id": 3948,
     *                          "name": "ООО Рога и Копыта",
     *                          "phone": "+79182225588",
     *                          "email": "test@test.ru",
     *                          "site": "www.mixcart.ru",
     *                          "address": "Россия, Московская область, Люберцы, улица Побратимов, владение 107",
     *                          "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                          "type_id": 2,
     *                          "type": "Поставщик",
     *                          "rating": 0,
     *                          "city": "Люберцы",
     *                          "administrative_area_level_1": "Московская область",
     *                          "country": "Россия",
     *                          "about": "",
     *                          "allow_editing": 1
     *                     },
     *                     "items":{
     *                          {
     *                              "id": 481057,
     *                              "product": "Тестовый товар, сам добавил",
     *                              "catalog_id": 3022,
     *                              "category_id": 0,
     *                              "price": 100,
     *                              "rating": 0,
     *                              "supplier": "ООО Рога и Копыта",
     *                              "brand": "",
     *                              "article": "1",
     *                              "ed": "шт",
     *                              "units": 0,
     *                              "currency": "RUB",
     *                              "image": "data:image/png;base64,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",
     *                              "in_basket": 0.25
     *                          }
     *                     }
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
     *              default={}
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