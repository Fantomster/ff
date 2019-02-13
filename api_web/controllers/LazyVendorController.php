<?php
/**
 * Date: 06.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\controllers;

use api_web\classes\LazyVendorWebApi;
use api_web\components\WebApiController;

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
     * @SWG\Post(path="/lazy-vendor/search",
     *     tags={"LazyVendor"},
     *     summary="Поиск ленивого поставщика",
     *     description="Поиск организаций с типом `Organization::TYPE_LAZY_VENDOR`",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"email":"lazy@vendor.ru"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                 {
     *                       "id": 1,
     *                       "name": "Рога и Копыта",
     *                       "legal_entity": "ООО Рога и Копыта",
     *                       "contact_name": "Имя контакта",
     *                       "phone": "+79251112233",
     *                       "email": "lazyvendor@supply.org",
     *                       "address": "Волгоградский пр., 1, Москва, Россия",
     *                       "image": "https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca",
     *                       "type_id": 4,
     *                       "type": "Поставщик",
     *                       "rating": 4.5,
     *                       "city": "Москва",
     *                       "administrative_area_level_1": "Московская область",
     *                       "country": "Россия",
     *                       "about": "Очень хорошая компания",
     *                       "inn": "0001112223",
     *                       "allow_editing": 1,
     *                       "min_order_price": 100,
     *                       "min_free_delivery_charge": 100,
     *                       "disabled_delivery_days": {
     *                                   1,
     *                                   2,
     *                                   3,
     *                                   5
     *                       },
     *                       "delivery_days": {
     *                            "mon": 0,
     *                            "tue": 1,
     *                            "wed": 0,
     *                            "thu": 0,
     *                            "fri": 0,
     *                            "sat": 1,
     *                            "sun": 1
     *                       }
     *                  }
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
    public function actionSearch()
    {
        $this->response = $this->classWebApi->search($this->request);
    }
}
