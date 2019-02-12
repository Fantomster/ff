<?php
/**
 * Date: 06.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\controllers;

use api_web\classes\LazyVendorWebApi;
use api_web\components\WebApiController;
use common\models\OrganizationContact;

/**
 * Class LazyVendorController
 *
 * @property LazyVendorWebApi $classWebApi
 * @package api_web\controllers
 */
class LazyVendorController extends WebApiController
{
    public $className = LazyVendorWebApi::class;

    /**
     * @SWG\Post(path="/lazy-vendor/create",
     *     tags={"LazyVendor"},
     *     summary="Создание нового ленивого поставщика",
     *     description="Создание нового ленивого поставщика",
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
     *                               "lazy-vendor":{
     *                                   "name": "name vendor",
     *                                   "address": "Россия, Москва, Привольная 70",
     *                                   "email":"test@test.ru",
     *                                   "phone": "+79182225588",
     *                                   "contact_name": "Контактное лицо",
     *                                   "inn": "12345678901",
     *                                   "additional_params":{
     *                                      "min_order_price": 2500,
     *                                      "delivery_price": 500,
     *                                      "delivery_discount_percent": 5,
     *                                      "discount_product": 10,
     *                                      "delivery_days": {
     *                                          "mon": 0,
     *                                          "tue": 1,
     *                                          "wed": 0,
     *                                          "thu": 0,
     *                                          "fri": 0,
     *                                          "sat": 1,
     *                                          "sun": 1
     *                                      }
     *                                   }
     *                               }
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                                   "name": "name vendor",
     *                                   "address": "Россия, Москва, Привольная 70",
     *                                   "email":"test@test.ru",
     *                                   "phone": "+79182225588",
     *                                   "contact_name": "Контактное лицо",
     *                                   "inn": "12345678901",
     *                                   "additional_params":{
     *                                      "min_order_price": 2500,
     *                                      "delivery_price": 500,
     *                                      "delivery_discount_percent": 5,
     *                                      "discount_product": 10,
     *                                      "delivery_days": {
     *                                          "mon": 0,
     *                                          "tue": 1,
     *                                          "wed": 0,
     *                                          "thu": 0,
     *                                          "fri": 0,
     *                                          "sat": 1,
     *                                          "sun": 1
     *                                      }
     *                                   }
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
     * @throws \Exception
     */
    public function actionCreate()
    {
        $this->response = $this->classWebApi->create($this->request);
    }

    /**
     * @SWG\Post(path="/lazy-vendor/list",
     *     tags={"LazyVendor"},
     *     summary="Список ленивых поставщиков",
     *     description="Список ленивых поставщиков",
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
     *                      "search": {
     *                          "query": "ООО Рога и копыта",
     *                          "address": "Москва"
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "-name"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                               "items":{
     *                                   {
     *                                       "id": 3998,
     *                                       "name": "name vendor",
     *                                       "address": "Россия, Москва, Привольная 70",
     *                                       "contact_count": 0,
     *                                       "product_count": {
     *                                          "all": 2,
     *                                          "allow": 1
     *                                       },
     *                                       "cat_id": 4173
     *                                 }
     *                               },
     *                                "pagination": {
     *                                  "page": 1,
     *                                  "page_size": 12
     *                              },
     *                          "sort": "name"
     *                      }
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
    public function actionList()
    {
        $this->response = $this->classWebApi->list($this->request);
    }

    /**
     * @SWG\Post(path="/lazy-vendor/contact-list",
     *     tags={"LazyVendor"},
     *     summary="Список контактов ленивых поставщиков",
     *     description="Список контактов ленивых поставщиков",
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
     *                      "id": 1,
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
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
     *                               "items":{
     *                                   {
     *                                       "id": 3998,
     *                                       "contact": "email@test.ru",
     *                                       "type": 1,
     *                                       "notification":{
     *                                          "order_created":1,
     *                                          "order_canceled":0,
     *                                          "order_changed":1,
     *                                          "order_done":0
     *                                       }
     *                                 },
     *                                 {
     *                                       "id": 2,
     *                                       "contact": "+79162807272",
     *                                       "type": 2,
     *                                       "notification":{
     *                                          "order_created":1,
     *                                          "order_canceled":0,
     *                                          "order_changed":1,
     *                                          "order_done":0
     *                                       }
     *                                 }
     *                               },
     *                               "pagination": {
     *                                  "page": 1,
     *                                  "page_size": 12
     *                              }
     *                      }
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
    public function actionContactList()
    {
        $this->response = $this->classWebApi->contactList($this->request);
    }

    /**
     * @SWG\Post(path="/lazy-vendor/contact-type-list",
     *     tags={"LazyVendor"},
     *     summary="Список типов контактов",
     *     description="Список типов контактов",
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
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                         1:"email",
     *                          2:"phone"
     *                      }
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
    public function actionContactTypeList()
    {
        $this->response = [
            OrganizationContact::TYPE_EMAIL => 'Email',
            OrganizationContact::TYPE_PHONE => 'Phone',
        ];
    }

    /**
     * @SWG\Post(path="/lazy-vendor/contact-check",
     *     tags={"LazyVendor"},
     *     summary="Отправка проверочного email или SMS",
     *     description="Отправка проверочного email или SMS",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                        "result":true
     *                      }
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
    public function actionContactCheck()
    {
        $this->response = $this->classWebApi->contactCheck($this->request);
    }
}
