<?php

namespace api_web\controllers;

use api_web\classes\GuideWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use yii\filters\AccessControl;

/**
 * Class GuideController
 *
 * @property GuideWebApi $classWebApi
 * @package api_web\controllers
 */
class GuideController extends WebApiController
{
    public $className = GuideWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => [
                        'list',
                        'get',
                        'add-to-cart'
                    ],
                    'roles'   => [Registry::PROCUREMENT_INITIATOR],
                ],
                [
                    'allow'   => true,
                    'actions' => [
                        'create',
                        'create-from-order',
                        'delete',
                        'rename',
                        'change-color',
                        'action-product',
                    ],
                    'roles'   => [Registry::PURCHASER_RESTAURANT],
                ],
                [
                    'allow'   => true,
                    'actions' => [
                        'get-products',
                    ],
                    'roles'   => [Registry::OPERATOR],
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/guide/get",
     *     tags={"Guide"},
     *     summary="Информация о шаблоне",
     *     description="Информация о шаблоне",
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
     *                       "guide_id": 1
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
    public function actionGet()
    {
        $this->response = $this->classWebApi->getInfo($this->request);
    }

    /**
     * @SWG\Post(path="/guide/list",
     *     tags={"Guide"},
     *     summary="Список шаблонов",
     *     description="Список шаблонов",
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
     *                               "product_list": false,
     *                               "search":{
     *                                   "vendors": {3803},
     *                                   "color":"FFFEEE",
     *                                   "create_date": {
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   },
     *                                   "updated_date": {
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   },
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"id, name, updated_date"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":{{
     *                   "id": 1,
     *                   "name": "Название шаблона",
     *                   "color": "FFEECC",
     *                   "created_at": "Mar 12, 2018",
     *                   "product_count": 1,
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
     *               }},
     *              "pagination": {
     *                   "page": 1,
     *                   "page_size": 12,
     *                   "total_page": 1
     *               }
     *          }),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionList()
    {
        $this->response = $this->classWebApi->getList($this->request);
    }

    /**
     * @SWG\Post(path="/guide/create",
     *     tags={"Guide"},
     *     summary="Создание шаблона",
     *     description="Создание нового шаблона для заказа",
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
     *                       "name": "Название шаблона",
     *                       "color": "FF00DD",
     *                      "products": {1,2,3,4}
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
     * @throws
     */
    public function actionCreate()
    {
        $this->response = $this->classWebApi->create($this->request);
    }

    /**
     * @SWG\Post(path="/guide/create-from-order",
     *     tags={"Guide"},
     *     summary="Создание шаблона из заказа",
     *     description="Создание нового шаблона из заказа",
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
     * @throws
     */
    public function actionCreateFromOrder()
    {
        $this->response = $this->classWebApi->createFromOrder($this->request);
    }

    /**
     * @SWG\Post(path="/guide/delete",
     *     tags={"Guide"},
     *     summary="Удалить шаблон",
     *     description="Удалить шаблон",
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
     *                      "guide_id": 1
     *                  }
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
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws
     */
    public function actionDelete()
    {
        $this->response = $this->classWebApi->delete($this->request);
    }

    /**
     * @SWG\Post(path="/guide/rename",
     *     tags={"Guide"},
     *     summary="Переименовать шаблон",
     *     description="Переименовать шаблон",
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
     *                      "name": "Новое Название шаблона",
     *                      "guide_id": 1
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
     * @throws
     */
    public function actionRename()
    {
        $this->response = $this->classWebApi->rename($this->request);
    }

    /**
     * @SWG\Post(path="/guide/change-color",
     *     tags={"Guide"},
     *     summary="Сменить цвет шаблона",
     *     description="Сменить цвет шаблона",
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
     *                      "color": "FFF000",
     *                      "guide_id": 1
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
     * @throws
     */
    public function actionChangeColor()
    {
        $this->response = $this->classWebApi->changeColorGuide($this->request);
    }

    /**
     * @SWG\Post(path="/guide/get-products",
     *     tags={"Guide"},
     *     summary="Список товаров в шаблоне",
     *     description="Список товаров в шаблоне",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="sort:
     * price
     * -price
     * product
     * -product
     * updated_at
     * -updated_at
     * vendor
     * -vendor",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "guide_id": 1,
     *                               "search":{
     *                                   "vendor_id": {1},
     *                                   "product": "Название товара",
     *                                   "price": {
     *                                          "from": 100,
     *                                          "to": 300,
     *                                    }
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"price"
     *                           }
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
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws
     */
    public function actionGetProducts()
    {
        $this->response = $this->classWebApi->getProducts($this->request);
    }

    /**
     * @SWG\Post(path="/guide/add-to-cart",
     *     tags={"Guide"},
     *     summary="Добавить шаблон в корзину",
     *     description="Добавить шаблон в корзину",
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
     *                      "guide_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *     @SWG\Schema(
     *              default={
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
     *                  }
     *              }
     *          )
     *      ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws
     */
    public function actionAddToCart()
    {
        $this->response = $this->classWebApi->addToCart($this->request);
    }

    /**
     * @SWG\Post(path="/guide/action-product",
     *     tags={"Guide"},
     *     summary="Добавить/Удалить продукт из шаблона",
     *     description="Добавить/Удалить продукт из шаблона",
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
     *                      "guide_id": 1,
     *                      "products": {
     *                          {
     *                              "operation": "add",
     *                              "product_id": 1
     *                          },
     *                          {
     *                              "operation": "del",
     *                              "product_id": 31
     *                          },
     *                          {
     *                              "operation": "add",
     *                              "product_id": 2
     *                          }
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
     *                   "success": 3,
     *                   "error": 0
     *              }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     * @throws
     */
    public function actionActionProduct()
    {
        $this->response = $this->classWebApi->actionProductFromGuide($this->request);
    }
}
