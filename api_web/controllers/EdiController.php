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
     *                             "start": "23.08.2018",
     *                             "end": "24.08.2018"
     *                         },
     *                         "completion_date": {
     *                             "start": "23.08.2018",
     *                             "end": "24.08.2018"
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
     *                          "id": 6064,
     *                          "created_at": "2017-09-27T03:00:00+03:00",
     *                          "completion_date": "2018-10-16T10:05:24+03:00",
     *                          "status": 8,
     *                          "status_text": "Отправлен поставщиком",
     *                          "vendor": "vasilkai2017@mail.ru",
     *                          "currency_id": 1,
     *                          "create_user": "Капотник",
     *                          "accept_user": "",
     *                          "edi_number": {
     *                              "1",
     *                              "2",
     *                              "3"
     *                          }
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function actionAcceptProducts()
    {
        $this->response = $this->container->get('EdiWebApi')->acceptProducts($this->request);
    }

    /**
     * @SWG\Post(path="/edi/order-complete",
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
     * @throws \Exception
     */
    public function actionOrderComplete()
    {
        $this->response = $this->container->get('EdiWebApi')->orderComplete($this->request);
    }

    /**
     * @SWG\Post(path="/edi/order-cancel",
     *     tags={"edi"},
     *     summary="Отмена заказа",
     *     description="Отмена заказа",
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
     * @throws \Exception
     */
    public function actionCancelOrder()
    {
        $this->response = $this->container->get('EdiWebApi')->cancelOrder($this->request);
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
     * @throws \Exception
     */
    public function actionHistoryCount()
    {
        $this->response = $this->container->get('EdiWebApi')->getHistoryCount($this->request);
    }


    /**
     * @SWG\Post(path="/edi/order-update",
     *     tags={"edi"},
     *     summary="Редактирование заказа EDI",
     *     description="Редактирование заказа EDI",
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
     *                      "order_id":1,
     *                      "products": {
     *                          {"operation":"edit", "id":1, "quantity":2},
     *                          {"operation":"edit", "id":2},
     *                          {"operation":"add", "id":3, "quantity":2},
     *                          {"operation":"delete", "id":4}
     *                       }
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
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
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
     *          }
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
    public function actionOrderUpdate()
    {
        $this->response = $this->container->get('OrderWebApi')->update($this->request);
    }


    /**
     * @SWG\Post(path="/edi/order-repeat",
     *     tags={"edi"},
     *     summary="Повторить заказ EDI",
     *     description="Повторить заказ EDI",
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
     *         @SWG\Schema(ref="#/definitions/CartItems")
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
    public function actionOrderRepeat()
    {
        $this->response = $this->container->get('OrderWebApi')->repeat($this->request);
    }


    /**
     * @SWG\Post(path="/edi/order-print-pdf",
     *     tags={"edi"},
     *     summary="Сохранить заказ EDI в PDF",
     *     description="Сохранить заказ EDI в PDF",
     *     produces={"application/json", "application/pdf"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1, "base64_encode":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description="Если все прошло хорошо вернет файл закодированый в base64",
     *         @SWG\Schema(
     *              default="JVBERi0xLjQKJeLjz9MKMyAwIG9iago8PC9UeXBlIC9QYWdlCi9QYXJlbnQgMSAwIFIKL01lZGlhQm94IFswIDAgNTk1LjI4MCA4NDEuOD"
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
     * @throws \Exception
     */
    public function actionOrderPrintPdf()
    {
        $result = $this->container->get('OrderWebApi')->saveToPdf($this->request, $this);
        if (is_array($result)) {
            $this->response = $result;
        } else {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers:Content-Type, Authorization');
            exit($result);
        }
    }


    /**
     * @SWG\Post(path="/edi/order-create-guide",
     *     tags={"edi"},
     *     summary="Создание шаблона из заказа EDI",
     *     description="Создание нового шаблона из заказа EDI",
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
     *                       "order_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                   "id": 1,
     *                   "name": "Название шаблона",
     *                   "color": "FFEECC",
     *                   "products": {
     *                       {
     *                           "id": 470371,
     *                           "product": "name",
     *                           "catalog_id": 2770,
     *                           "price": 678,
     *                           "discount_price": 0,
     *                           "rating": 0,
     *                           "supplier": "kjghkjgkj",
     *                           "brand": "",
     *                           "article": "1",
     *                           "ed": "432",
     *                           "units": 1,
     *                           "currency": "RUB",
     *                           "image": "url_to_image",
     *                           "in_basket": 0
     *                       }
     *                   }
     *               }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionOrderCreateGuide()
    {
        $this->response = $this->container->get('GuideWebApi')->createFromOrder($this->request);
    }

    /**
     * @SWG\Post(path="/edi/status-list",
     *     tags={"edi"},
     *     summary="Статусы заказа EDI",
     *     description="Статусы заказа EDI",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  {
     *                      "id": 1,
     *                      "title": "Ожидает подтверждения поставщика"
     *                  },
     *                  {
     *                      "id": 3,
     *                      "title": "Выполняется"
     *                  },
     *                  {
     *                      "id": 8,
     *                      "title": "Отправлен поставщиком"
     *                  },
     *                  {
     *                      "id": 9,
     *                      "title": "Приемка завершена"
     *                  },
     *                  {
     *                      "id": 4,
     *                      "title": "Завершен"
     *                  },
     *                  {
     *                      "id": 6,
     *                      "title": "Отклонен поставщиком"
     *                  },
     *                  {
     *                      "id": 2,
     *                      "title": "Ожидает подтверждения клиента"
     *                  },
     *                  {
     *                      "id": 5,
     *                      "title": "Отклонен поставщиком"
     *                  }
     *              }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionStatusList()
    {
        $result = [];
        foreach ((new \common\models\Order)->getStatusListEdo() as $key => $value) {
            $result[] = ['id' => (int)$key, 'title' => $value];
        }
        $this->response = $result;
    }


    /**
     * @SWG\Post(path="/edi/save-to-pdf",
     *     tags={"edi"},
     *     summary="Сохранить заказ в PDF",
     *     description="Сохранить заказ в PDF",
     *     produces={"application/json", "application/pdf"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1, "base64_encode":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description="Если все прошло хорошо вернет файл закодированый в base64",
     *         @SWG\Schema(
     *              default="JVBERi0xLjQKJeLjz9MKMyAwIG9iago8PC9UeXBlIC9QYWdlCi9QYXJlbnQgMSAwIFIKL01lZGlhQm94IFswIDAgNTk1LjI4MCA4NDEuOD"
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
     * @throws \Exception
     */
    public function actionSaveToPdf()
    {
        $result = $this->container->get('OrderWebApi')->saveToPdf($this->request, $this);
        if (is_array($result)) {
            $this->response = $result;
        } else {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers:Content-Type, Authorization');
            exit($result);
        }
    }
}