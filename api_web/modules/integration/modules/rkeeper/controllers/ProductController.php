<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\rkeeper\models\rkeeperProduct;

class ProductController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/rkeeper/product/get",
     *     tags={"Integration/rkeeper/product"},
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
     *                          "acc": 4422,
     *                          "rid": 376,
     *                          "group_name": "Товар",
     *                          "group_rid": 46,
     *                          "product_type": 1,
     *                          "unitname": "кг",
     *                          "unit_rid": 1
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
        $this->response = (new rkeeperProduct())->get($this->request);
    }

    /**
     * @SWG\Post(path="/integration/rkeeper/product/list",
     *     tags={"Integration/rkeeper/product"},
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
     *                          "acc": 4422,
     *                          "rid": 376,
     *                          "group_name": "Товар",
     *                          "group_rid": 46,
     *                          "product_type": 1,
     *                          "unitname": "кг",
     *                          "unit_rid": 1}
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
        $this->response = (new rkeeperProduct())->list($this->request);
    }
}