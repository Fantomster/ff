<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:31 PM
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class CartController
 * @package api_web\controllers
 */
class RabbitController extends WebApiController
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
    public function actionAddToQueue()
    {
        $this->response = $this->container->get('RabbitWebApi')->dispatch($this->request);
    }
}