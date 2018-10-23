<?php

namespace api_web\modules\integration\controllers;

class DefaultController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/default/service-list",
     *     tags={"Integration"},
     *     summary="Список сервисов интерграции",
     *     description="Список сервисов интерграции",
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
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationServiceList"),
     *     )
     * )
     * @throws \Exception
     */
    public function actionServiceList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->list($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/user-service-set",
     *     tags={"Integration"},
     *     summary="Установить ИД интеграции по умолчанию для юзера",
     *     description="Установить ИД интеграцию по умолчанию для юзера",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"service_id":2}
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": true
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
     * )
     * @throws \Exception
     */
    public function actionUserServiceSet()
    {
        $this->response = $this->container->get('IntegrationWebApi')->userServiceSet($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/map-list",
     *     tags={"Integration"},
     *     summary="Получение списка сопоставления со всеми связями ",
     *     description="Получение списка сопоставления со всеми связями ",
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
     *                      "service_id": 1,
     *                      "search": {
     *                              "product": "Апельсины",
     *                               "vendor": 1,
     *                         },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "products" : {
     *                       {
     *                      "id": 22666,
     *                       "service_id": 2,
     *                       "organization_id": 1,
     *                       "vendor_id": 1,
     *                       "product": {
     *                          "id": 1,
     *                          "name": "продукт из МС"
     *                       },
     *                       "unit": {
     *                          "name": "кг"
     *                       },
     *                       "outer_product": {
     *                       "id": 1,
     *                          "name": "продукт из у.с."
     *                       },
     *                       "outer_unit": {
     *                          "id": 1,
     *                          "name": "кг"
     *                       },
     *                       "outer_store": {
     *                          "id": 1,
     *                          "name": "Основной склад у.с."
     *                       },
     *                       "coefficient": 2,
     *                       "vat":10,
     *                       "created_at": "2018-09-04T09:55:22+03:00",
     *                       "updated_at": "2018-09-04T09:55:22+03:00"
     *                       }
     *                       },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 17
     *                  },
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
     * )
     * @throws \Exception
     */
    public function actionMapList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->getProductMapList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/default/map-update",
     *     tags={"Integration"},
     *     summary="Изменение атрибутов сопоставления",
     *     description="Изменение атрибутов сопоставления",
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
     *                          "service_id": 2,
     *                          "map": {
     *                              {
     *                                  "product_id": 2234,
     *                                  "outer_product_id": 102,
     *                                  "outer_store_id": 1056,
     *                                  "coefficient": 2,
     *                                  "vat": 10
     *                              },
     *                              {
     *                                  "product_id": 4234,
     *                                  "outer_product_id": 132,
     *                                  "outer_store_id": 2076,
     *                                  "coefficient": 1,
     *                                  "vat": 18
     *                              }
     *                          }
     *                       }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "2234" : {
     *                      "success": true,
     *                       },
     *                  "4234": {
     *                      "success": false,
     *                      "error": "text error",
     *                  },
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
     * )
     * @throws \Exception
     */
    public function actionMapUpdate()
    {
        $this->response = $this->container->get('IntegrationWebApi')->mapUpdate($this->request);
    }
}