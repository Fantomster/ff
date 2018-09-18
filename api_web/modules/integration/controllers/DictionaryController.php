<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:52 AM
 */

namespace api_web\modules\integration\controllers;


use api_web\modules\integration\classes\Dictionary;

class DictionaryController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/dictionary/product-list",
     *     tags={"Integration/iiko/product"},
     *     summary="Список продуктов",
     *     description="Список продуктов",
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
     *                      "service_id": 2,
     *                      "search": {
     *                          "name": "название"
     *                      },
     *                      "pagination":{
     *                          "page": 1,
     *                          "page_size": 12
     *                      }
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       "products": {{
     *                          "id": 1,
     *                          "name": "Бананы",
     *                          "num": "0004",
     *                          "code": "8",
     *                          "product_type": "GOODS",
     *                          "cooking_place_type": "Кухня",
     *                          "unit": "кг",
     *                          "is_active":1}
     *                      },
     *                      "pagination": {
     *                                      "page": 1,
     *                                      "total_page": 17,
     *                                      "page_size": 12
     *                                  }
     *              }
     *          )
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
    public function actionProductList()
    {
        $this->response = (new Dictionary($this->request['service_id'], 'Product'))->productList($this->request);
    }
}