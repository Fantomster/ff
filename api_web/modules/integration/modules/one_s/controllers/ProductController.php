<?php

namespace api_web\modules\integration\modules\one_s\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\one_s\models\one_sProduct;

class ProductController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/one_s/product/list",
     *     tags={"Integration/one_s/product"},
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
    public function actionList()
    {
        $this->response = (new one_sProduct())->list($this->request);
    }
}