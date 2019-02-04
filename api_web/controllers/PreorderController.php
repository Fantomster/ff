<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:34
 */

namespace api_web\controllers;

use api_web\classes\PreorderWebApi;
use api_web\components\WebApiController;

/**
 * Class PreorderController
 *
 * @property PreorderWebApi $classWebApi
 * @package api_web\controllers
 */
class PreorderController extends WebApiController
{
    public $className = PreorderWebApi::class;

    /**
     * @SWG\Post(path="/preorder/create",
     *     tags={"Preorder"},
     *     summary="Создание предзаказа",
     *     description="Создание предзаказа",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "vendor_id": 50
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={
     *                          "id": 1,
     *                          "is_active": true,
     *                          "organization": {
     *                              "id": 1,
     *                              "name": "Ресторан 1"
     *                          },
     *                          "user": {
     *                              "id": 1,
     *                              "name": "Иванов Иван"
     *                          },
     *                          "count": {
     *                              "produtcs": 18,
     *                              "orders": 4
     *                          },
     *                          "summ": "250000,50",
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          },
     *                          "created_at": "2017-10-06T23:00:00+03:00",
     *                          "upated_at": "2017-10-06T23:00:00+03:00"
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionCreate()
    {
        $this->response = $this->classWebApi->create($this->request);
    }
}
