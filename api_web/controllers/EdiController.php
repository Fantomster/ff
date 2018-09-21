<?php

/**
 * Class EdiController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-11
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class EdiController
 * @package api_web\controllers
 */
class EdiController extends WebApiController
{

    /**
     * @SWG\Post(path="/edi/order-history",
     *     tags={"edi"},
     *     summary="Данные заказов в системе EDI",
     *     description="Данные заказов в системе EDI",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "search": {
     *                         "vendor": {
     *                             124,
     *                             143
     *                         },
     *                         "status": {
     *                             1,
     *                             2
     *                         },
     *                         "create_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         },
     *                         "completion_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         }
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "id"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "orders": {
     *                      {
     *                          "id": 12,
     *                          "created_at": "10.10.2016",
     *                          "status_updated_at": "10.10.2016",
     *                          "status": 1,
     *                          "status_text": "Ожидает подтверждения поставщика",
     *                          "vendor": "POSTAVOK.NET CORPORATION",
     *                          "create_user": "Admin",
     *                          "comment": "Коментарий утерян в ящике стола"
     *                      },
     *                      {
     *                          "id": 14,
     *                          "created_at": "10.10.2016",
     *                          "status_updated_at": "11.10.2016",
     *                          "status": 1,
     *                          "status_text": "Ожидает подтверждения поставщика",
     *                          "vendor": "POSTAVKA CORP INC",
     *                          "create_user": "Vasya",
     *                          "comment": "Коментариев гнет"
     *                      }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 17
     *                  },
     *                  "pagination": "-name"
     *              }
     *         )
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
    public function actionOrderHistory()
    {
        $this->response = $this->container->get('EdiWebApi')->getOrderHistory($this->request);
    }

    /**
     * @SWG\Post(path="/edi/order-info",
     *     tags={"edi"},
     *     summary="Карточка заказа в системе EDI",
     *     description="Карточка заказа в системе EDI",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "order_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "action": {
     *                      "edit": false,
     *                      "cancel": false,
     *                      "complete": true
     *                  },
     *                  "order": {
     *                      "id": 1,
     *                      "total_price": "76.88",
     *                      "invoice_relation": null,
     *                      "created_at": "2016-09-28 15:22:20",
     *                      "requested_delivery": null,
     *                      "actual_delivery": null,
     *                      "comment": "",
     *                      "discount": null,
     *                      "completion_date": null,
     *                      "order_code": null,
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "status_id": 1,
     *                      "status_text": "Ожидает подтверждения поставщика",
     *                      "position_count": 2,
     *                      "delivery_price": 0,
     *                      "min_order_price": 3191,
     *                      "total_price_without_discount": "3591.22",
     *                      "create_user": "Михаил Нарзяев",
     *                      "accept_user": "Федор плотников",
     *                      "items": {
     *                          {
     *                              "id": 18606,
     *                              "product": "ааа",
     *                              "product_id": 481037,
     *                              "catalog_id": 3010,
     *                              "price": 100,
     *                              "difference": {
     *                                  "class": "down",
     *                                  "price": 95
     *                              },
     *                              "quantity": "0.1",
     *                              "comment": "",
     *                              "total": 10,
     *                              "rating": 0,
     *                              "brand": "",
     *                              "article": "АБВ",
     *                              "ed": "бутылка",
     *                              "units": null,
     *                              "currency": "RUB",
     *                              "currency_id": 1,
     *                              "image": "https://mixcart.ru/fmarket/images/product_placeholder.jpg"
     *                          },
     *                          {
     *                              "id": 18607,
     *                              "product": "ааа2",
     *                              "product_id": 481034,
     *                              "catalog_id": 3011,
     *                              "price": "120.23",
     *                              "difference": {
     *                                  "class":"down",
     *                                  "price":95
     *                              },
     *                              "quantity": 0.12,
     *                              "comment": "Новый товар",
     *                              "total": 101,
     *                              "rating": 12,
     *                              "brand": "",
     *                              "article": "АБВ",
     *                              "ed": "бутылка",
     *                              "units": null,
     *                              "currency": "RUB",
     *                              "currency_id": 1,
     *                              "image": "https://mixcart.ru/fmarket/images/product_placeholder2.jpg"
     *                          }
     *                      },
     *                      "client": {
     *                          "id": 1,
     *                          "name": "Космическая пятница",
     *                          "legal_entity": "ООО 'Космическая пятница'",
     *                          "contact_name": "Космический Чел",
     *                          "phone": "+7 918 222-55-88",
     *                          "email": "neo@neo.com",
     *                          "site": "mixcart.ru",
     *                          "address": "ул. Побратимов, 7, Люберцы, Московская обл., Россия, 140013",
     *                          "image": "https://fkeeper.s3.amazonaws.com/org-picture/53beaf2b075e33f1fcffeb3505ef1765.jpg",
     *                          "type_id": 1,
     *                          "type": "Ресторан",
     *                          "rating": 0,
     *                          "house": "7",
     *                          "route": "улица Побратимов",
     *                          "city": "Люберцы",
     *                          "administrative_area_level_1": "Московская область",
     *                          "country": "Россия",
     *                          "place_id": "ChIJM4NYCODJSkERVeMzXqoIJho",
     *                          "about": "asd",
     *                          "is_allowed_for_franchisee": 1
     *                      },
     *                      "vendor": {
     *                          "id": 3803,
     *                          "name": "EL Поставщик",
     *                          "legal_entity": "",
     *                          "contact_name": "Контактное лицо",
     *                          "phone": "+7 918 222-55-88",
     *                          "email": "kosm@test.ru",
     *                          "site": "www.mixcart.ru",
     *                          "address": "ул. Побратимов, 7, Люберцы, Московская обл., Россия, 140013",
     *                          "image": "https://fkeeper.s3.amazonaws.com/org-picture/3ae0a1b32b4a81eb7a1ac12e4b220205.jpg",
     *                          "type_id": 2,
     *                          "type": "Поставщик",
     *                          "rating": 0,
     *                          "house": "7",
     *                          "route": "улица Побратимов",
     *                          "city": "Люберцы",
     *                          "administrative_area_level_1": "Московская область",
     *                          "country": "Россия",
     *                          "place_id": "ChIJM4NYCODJSkERVeMzXqoIJho",
     *                          "about": "",
     *                          "is_allowed_for_franchisee": 1,
     *                          "inn": "0001112223",
     *                          "allow_editing": 1,
     *                          "min_order_price": 0,
     *                          "min_free_delivery_charge": 0,
     *                          "disabled_delivery_days": {
     *                              1,
     *                              3,
     *                              4,
     *                              5
     *                          },
     *                          "delivery_days": {
     *                              "mon": 0,
     *                              "tue": 1,
     *                              "wed": 0,
     *                              "thu": 0,
     *                              "fri": 0,
     *                              "sat": 1,
     *                              "sun": 1
     *                          }
     *                      }
     *                  }
     *              }
     *         )
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
    public function actionOrderInfo()
    {
        $this->response = $this->container->get('EdiWebApi')->getOrderInfo($this->request);
    }

    /**
     * @SWG\Post(path="/edi/accept-products",
     *     tags={"edi"},
     *     summary="Завершение приемки товаров по заказу",
     *     description="Завершение приемки товаров по заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "order_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      true
     *              }
     *         )
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
    public function actionAcceptProducts()
    {
        $this->response = $this->container->get('EdiWebApi')->acceptProducts($this->request);
    }

    /**
     * @SWG\Post(path="/edi/finish-order",
     *     tags={"edi"},
     *     summary="Завершение заказа",
     *     description="Завершение заказа",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "order_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      true
     *              }
     *         )
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
    public function actionFinishOrder()
    {
        $this->response = $this->container->get('EdiWebApi')->finishOrder($this->request);
    }

    /**
     * @SWG\Post(path="/edi/history-count",
     *     tags={"edi"},
     *     summary="История заказов EDI в цифрах",
     *     description="История заказов EDI в цифрах",
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
     *          response = 200,
     *          description = "success",
     *          @SWG\Schema(
     *              default={
     *                   "waiting": 61,
     *                   "processing": 3,
     *                   "sent_by_vendor": 3,
     *                   "acceptance_finished": 21,
     *                   "success": 2,
     *                   "canceled": 3
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
     */
    public function actionHistoryCount()
    {
        $this->response = $this->container->get('EdiWebApi')->getHistoryCount($this->request);
    }

}