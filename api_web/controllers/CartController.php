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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{"product_id":1, "quantity":10}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/CartItems"),
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/CartItems"),
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
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"vendor_id": 3803}
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

    /**
     * @SWG\Post(path="/cart/registration",
     *     tags={"Cart"},
     *     summary="Зарегистрировать заказ из корзины",
     *     description="Зарегистрировать заказ из корзины",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {{"id":1, "delivery_date":"d.m.Y", "comment":"comment order"}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={3301, 3302}
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
        $this->response = $this->container->get('CartWebApi')->register($this->request);
    }

    /**
     * @SWG\Post(path="/cart/product-comment",
     *     tags={"Cart"},
     *     summary="Добавить комментарий к позиции",
     *     description="Добавить комментарий к позиции",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"product_id": 1, "comment":"New comment!"}
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
    public function actionProductComment()
    {
        $this->response = $this->container->get('CartWebApi')->productComment($this->request);
    }
}