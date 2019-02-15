<?php
/**
 * Date: 14.02.2019
 * Author: Mike N.
 * Time: 12:15
 */

namespace api_web\controllers;

use api_web\classes\AnalogWebApi;
use api_web\components\WebApiController;

/**
 * Class AnalogController
 *
 * @property AnalogWebApi $classWebApi
 * @package api_web\controllers
 */
class AnalogController extends WebApiController
{
    public $className = AnalogWebApi::class;

    /**
     * @SWG\Post(path="/analog/list",
     *     tags={"Analog"},
     *     summary="Список аналогов",
     *     description="Список аналогов",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "pagination": {
     *                         "page": 1,
     *                         "page_size": 12
     *                     }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "items": {
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "total_page": 17,
     *                      "page_size": 12
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
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionList()
    {
        $this->response = $this->classWebApi->getList($this->request);
    }

    /**
     * @SWG\Post(path="/analog/get-product-analog-list",
     *     tags={"Analog"},
     *     summary="Список аналогов конкретного продукта",
     *     description="Список аналогов конкретного продукта",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                     "product_id": 524910
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "items": {
     *                      {
     *                          "product_id": "524910",
     *                          "product_name": "Test Product",
     *                          "article": "A5626292411",
     *                          "vendor_id": "3998",
     *                          "vendor_name": "name vendor",
     *                          "price": "500.00"
     *                      },
     *                      {
     *                          "product_id": "524913",
     *                          "product_name": "Test Product 2",
     *                          "article": "A5626292412",
     *                          "vendor_id": "3998",
     *                          "vendor_name": "name vendor",
     *                          "price": "500.00"
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
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws \Exception
     */
    public function actionGetProductAnalogList()
    {
        $this->response = $this->classWebApi->getProductAnalogList($this->request);
    }
}
