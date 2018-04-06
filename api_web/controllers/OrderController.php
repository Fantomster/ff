<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class OrderController
 * @package api_web\controllers
 */
class OrderController extends WebApiController
{
    /**
     * @SWG\Post(path="/order/registration",
     *     tags={"Order"},
     *     summary="Оформление заказа",
     *     description="Оформляем заказы которые передали в параметрах",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"orders":{1,2,3,4}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={{"order_id":1, "result":1},{"order_id":2, "result":1}, {"order_id":3, "result":1}}
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
    public function actionRegistration()
    {
        $this->response = $this->container->get('OrderWebApi')->registration($this->request);
    }

    /**
     * @SWG\Post(path="/order/comment",
     *     tags={"Order"},
     *     summary="Комментарий к заказу",
     *     description="Оставляем комментарий к заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1,"comment":"Тестовый комментарий"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"order_id":1, "comment":"Тестовый комментарий"}
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
    public function actionComment()
    {
        $this->response = $this->container->get('OrderWebApi')->addComment($this->request);
    }

    /**
     * @SWG\Post(path="/order/product-comment",
     *     tags={"Order"},
     *     summary="Комментарий к продукту в заказе",
     *     description="Оставляем комментарий к конкретному продукту в заказе",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1, "product_id":2, "comment":"Тестовый комментарий"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"order_id":1, "product_id":2, "comment":"Тестовый комментарий"}
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
    public function actionProductComment()
    {
        $this->response = $this->container->get('OrderWebApi')->addProductComment($this->request);
    }

    /**
     * @SWG\Post(path="/order/info",
     *     tags={"Order"},
     *     summary="Информация о заказе",
     *     description="Полная информация о заказе",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"order":{}}
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
    public function actionInfo()
    {
        $this->response = $this->container->get('OrderWebApi')->getInfo($this->request);
    }

    /**
     * @SWG\Post(path="/order/history",
     *     tags={"Order"},
     *     summary="История заказов",
     *     description="Список заказов текущего пользователя",
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
     *                                   "vendor": 1,
     *                                   "create_date": {
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   },
     *                                  "completion_date":{
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   }
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"id"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"orders":{}}
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
    public function actionHistory()
    {
        $this->response = $this->container->get('OrderWebApi')->getHistory($this->request);
    }

    /**
     * @SWG\Post(path="/order/products",
     *     tags={"Order"},
     *     summary="Список товаров доступных для заказа",
     *     description="Получить список товаров достпных для заказа",
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
     *                                   "product":"искомая строка",
     *                                   "category_id": {24, 17},
     *                                   "supplier_id": {3803, 4},
     *                                   "price": {"start":100, "end":300},
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-product"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *      {
     *          "headers":{
     *                  "id": "ID",
    "product": "Название"
     *          },
     *          "products":{
    {
    "id": "5269",
    "product": "Треска горячего копчения",
    "article": "457",
    "supplier": "ООО Рога и Копыта",
    "supp_org_id": 4,
    "cat_id": "3",
     *                                  "category_id": 24,
    "price": 499.80,
    "ed": "шт.",
    "currency": "RUB",
    "image":"https://mixcart.ru/fmarket/images/image-category/51.jpg",
    "in_basket": 0
    }
     *          },
     *          "pagination":{
     *              "page":1,
     *              "total_page":17,
     *              "page_size":12
     *          },
     *          "sort":"-product"
     *     }
     *            ),
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
    public function actionProducts()
    {
        $this->response = $this->container->get('OrderWebApi')->products($this->request);
    }

    /**
     * @SWG\Post(path="/order/status-list",
     *     tags={"Order"},
     *     summary="Список статусов заказа",
     *     description="Список статусов заказа",
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
     *              default=
     *              {
     *                 {
     *                      "id": 1,
     *                      "title": "Ожидает подтверждения поставщика"
     *                 },
     *                 {
     *                      "id": 2,
     *                      "title": "Ожидает подтверждения клиента"
     *                 }
     *              }
     *         ),
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
    public function actionStatusList()
    {
        $result = [];
        foreach ((new \common\models\Order)->getStatusList() as $key => $value) {
            $result[] = ['id' => (int)$key, 'title' => $value];
        }
        $this->response = $result;
    }

    /**
     * @SWG\Post(path="/order/cancel",
     *     tags={"Order"},
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
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":true}
     *         ),
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
    public function actionCancel()
    {
        $this->response = $this->container->get('OrderWebApi')->cancel($this->request);
    }

    /**
     * @SWG\Post(path="/order/repeat",
     *     tags={"Order"},
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
     *                  default={"order_id":1}
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
    public function actionRepeat()
    {
        $this->response = $this->container->get('OrderWebApi')->repeat($this->request);
    }

    /**
     * @SWG\Post(path="/order/complete",
     *     tags={"Order"},
     *     summary="Завершить заказ",
     *     description="Завершить заказ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *              {
     *                   "id": 6618,
     *                   "total_price": 510.00,
     *                   "invoice_relation": null,
     *                   "created_at": "2018-04-02 12:33:44",
     *                   "requested_delivery": "2018-04-02 19:00:00",
     *                   "actual_delivery": "2018-04-04 12:34:21",
     *                   "comment": "",
     *                   "discount": 0.00,
     *                   "completion_date": null,
     *                   "currency": "RUB",
     *                   "currency_id": 1,
     *                   "status_text": "Завершен",
     *                   "position_count": 1,
     *                   "delivery_price": 0,
     *                   "min_order_price": 0,
     *                   "total_price_without_discount": 510,
     *                   "items": {
     *                      {
     *                         "id": 18204,
     *                         "product": "post1@post.ru",
     *                         "product_id": 481059,
     *                         "catalog_id": 3026,
     *                         "price": 100,
     *                         "quantity": 5,
     *                         "comment": "",
     *                         "total": 510,
     *                         "rating": 0,
     *                         "brand": "",
     *                         "article": "1",
     *                         "ed": "in",
     *                         "currency": "RUB",
     *                         "currency_id": 1,
     *                         "image": "data:image/png;base64,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"
     *                      }
     *                   },
     *                   "client": {
     *                      "id": 1,
     *                      "name": "Космическая пятница",
     *                      "phone": "",
     *                      "email": "investor@f-keeper.ru",
     *                      "site": "",
     *                      "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                      "image": "https://fkeeper.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
     *                      "type_id": 1,
     *                      "type": "Ресторан",
     *                      "rating": 0,
     *                      "city": "Казань",
     *                      "administrative_area_level_1": "Республика Татарстан",
     *                      "country": "Россия",
     *                      "about": ""
     *                   },
     *                   "vendor": {
     *                      "id": 3950,
     *                      "name": "post1@post.ru",
     *                      "phone": "",
     *                      "email": null,
     *                      "site": "",
     *                      "address": "",
     *                      "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif",
     *                      "type_id": 2,
     *                      "type": "Поставщик",
     *                      "rating": 0,
     *                      "city": null,
     *                      "administrative_area_level_1": null,
     *                      "country": null,
     *                      "about": "",
     *                      "allow_editing": 1
     *                   }
     *           }
     *     }
     *         ),
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
    public function actionComplete()
    {
        $this->response = $this->container->get('OrderWebApi')->complete($this->request);
    }
}