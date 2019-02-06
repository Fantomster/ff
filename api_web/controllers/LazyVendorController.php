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
}
