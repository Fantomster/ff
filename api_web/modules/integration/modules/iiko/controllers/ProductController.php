<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\iiko\models\iikoProduct;

class ProductController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/iiko/product/get",
     *     tags={"Integration/iiko/product"},
     *     summary="Информация о продукте",
     *     description="Информация о продукте",
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
     *                      "id":1
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       {
     *                          "id": 1,
     *                          "name": "Бананы",
     *                          "num": "0004",
     *                          "code": "8",
     *                          "product_type": "GOODS",
     *                          "cooking_place_type": "Кухня",
     *                          "unit": "кг",
     *                          "is_active":1
     *                      }
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
    public function actionGet()
    {
        $this->response = (new iikoProduct())->get($this->request);
    }

    /**
     * @SWG\Post(path="/integration/iiko/product/list",
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
     *                      "search": {
     *                          "name": "название",
     *                          "is_active": 1
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
    public function actionList()
    {
        $this->response = (new iikoProduct())->list($this->request);
    }
}