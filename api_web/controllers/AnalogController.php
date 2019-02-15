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
     *                     },
     *                     "sort":"vendor_name"
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
     *                        {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product",
     *                              "ed": "кг",
     *                              "price": "500.00",
     *                              "article": "A5626292411",
     *                              "coefficient": 1.000001,
     *                              "analog_group": 1,
     *                              "sort_value": 1
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       },
     *                       {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product 1",
     *                              "ed": "кг",
     *                              "price": "501.00",
     *                              "article": "A5626292412",
     *                              "coefficient": null,
     *                              "analog_group": null,
     *                              "sort_value": null
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "total_page": 17,
     *                      "page_size": 12
     *                  },
     *                  "sort":"vendor_name"
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
     * @SWG\Post(path="/analog/group-list",
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
     *                     },
     *                     "sort":"vendor_name"
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
     *                        {
     *                          "analog_count": 2,
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product",
     *                              "ed": "кг",
     *                              "price": "500.00",
     *                              "article": "A5626292411",
     *                              "coefficient": 1.000001,
     *                              "analog_group": 1,
     *                              "sort_value": 1
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       },
     *                       {
     *                          "analog_count": 2,
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product 1",
     *                              "ed": "кг",
     *                              "price": "501.00",
     *                              "article": "A5626292412",
     *                              "coefficient": null,
     *                              "analog_group": null,
     *                              "sort_value": null
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "total_page": 17,
     *                      "page_size": 12
     *                  },
     *                  "sort":"vendor_name"
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
    public function actionGroupList()
    {
        $this->response = $this->classWebApi->getProductAnalogListGroup($this->request);
    }

    /**
     * @SWG\Post(path="/analog/get-list-sort-fields",
     *     tags={"Analog"},
     *     summary="Список полей сортировок аналогов",
     *     description="Список полей сортировок аналогов",
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
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "field":"title",
     *                  "field1":"title",
     *                  "field2":"title",
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
    public function actionGetListSortFields()
    {
        $this->response = $this->classWebApi->getListSortFields();
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
     *                        {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product",
     *                              "ed": "кг",
     *                              "price": "500.00",
     *                              "article": "A5626292411",
     *                              "coefficient": 1.000001,
     *                              "analog_group": 1,
     *                              "sort_value": 1
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       },
     *                       {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product 1",
     *                              "ed": "кг",
     *                              "price": "501.00",
     *                              "article": "A5626292412",
     *                              "coefficient": null,
     *                              "analog_group": null,
     *                              "sort_value": null
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       }
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

    /**
     * @SWG\Post(path="/analog/save-group",
     *     tags={"Analog"},
     *     summary="Сохранение группы аналогов",
     *     description="Сохранение группы аналогов",
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
     *                        {
     *                          "id": 524910,
     *                          "coefficient": 1.000001,
     *                          "analog_group": 1,
     *                          "sort_value": 1
     *                       },
     *                       {
     *                          "id": 524909,
     *                          "coefficient": 2,
     *                          "analog_group": 1,
     *                          "sort_value": 2
     *                       },
     *              }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "items": {
     *                        {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product",
     *                              "ed": "кг",
     *                              "price": "500.00",
     *                              "article": "A5626292411",
     *                              "coefficient": 1.000001,
     *                              "analog_group": 1,
     *                              "sort_value": 1
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       },
     *                       {
     *                          "product": {
     *                              "id": 524910,
     *                              "name": "Test Product 1",
     *                              "ed": "кг",
     *                              "price": "501.00",
     *                              "article": "A5626292412",
     *                              "coefficient": null,
     *                              "analog_group": null,
     *                              "sort_value": null
     *                          },
     *                          "vendor": {
     *                              "id": 3998,
     *                              "name": "name vendor"
     *                          },
     *                          "currency": {
     *                              "id": 1,
     *                              "symbol": "RUB"
     *                          }
     *                       }
     *                  }
     *                  "items_delete":{15523, 156687}
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
     * @throws
     */
    public function actionSaveGroup()
    {
        $this->response = $this->classWebApi->saveGroup($this->request);
    }
}
