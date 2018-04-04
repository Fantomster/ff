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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              default={}
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     *              default={}
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
    public function actionRepeat()
    {
        $this->response = $this->container->get('OrderWebApi')->repeat($this->request);
    }
}