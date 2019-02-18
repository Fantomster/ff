<?php
/**
 * Date: 13.02.2019
 * Author: Mike N.
 * Time: 15:18
 */

namespace api_web\controllers;

use api_web\classes\LazyVendorPriceWebApi;
use api_web\classes\LazyVendorWebApi;
use api_web\components\WebApiController;

/**
 * Class LazyVendorPriceController
 *
 * @property LazyVendorPriceWebApi $classWebApi
 * @package api_web\controllers
 */
class LazyVendorPriceController extends WebApiController
{
    public $className = LazyVendorPriceWebApi::class;

    /**
     * @SWG\Post(path="/lazy-vendor/price/get",
     *     tags={"LazyVendor/Price"},
     *     summary="Прайс лист",
     *     description="Прайс лист",
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
     *                      "vendor_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "catalog": {
     *                      "id": 4173,
     *                      "name": "Космическая пятница_LC",
     *                      "vendor_name": "name vendor"
     *                  },
     *                  "items":{
     *                  {
     *                          "article": "A5626292411",
     *                          "name": "Test Product",
     *                          "category": {
     *                              "id": 3,
     *                              "name": "Говядина"
     *                          },
     *                          "price": "500.00",
     *                          "ed": "кг",
     *                          "units": 1,
     *                          "status": 1,
     *                          "picture": null,
     *                          "attr": {
     *                              "cg_id": 733447,
     *                              "cbg_id": 524910
     *                          }
     *                      }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12
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
    public function actionGet()
    {
        $this->response = $this->classWebApi->get($this->request);
    }

    /**
     * @SWG\Post(path="/lazy-vendor/price/delete",
     *     tags={"LazyVendor/Price"},
     *     summary="Удаление индивидуального каталога",
     *     description="Удаление индивидуального каталога",
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
     *                      "vendor_id": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "result": true
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
    public function actionDelete()
    {
        $this->response = $this->classWebApi->deletePriceList($this->request);
    }
}
