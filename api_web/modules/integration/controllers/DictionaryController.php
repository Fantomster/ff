<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:52 AM
 */

namespace api_web\modules\integration\controllers;

use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\classes\Integration;
use yii\web\BadRequestHttpException;

class DictionaryController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/dictionary/list",
     *     tags={"Integration/dictionary"},
     *     summary="Список справочников",
     *     description="Список справочников",
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
     *                      "service_id": 2
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                          {
     *                              "id": 6,
     *                              "name": "agent",
     *                              "title": "Контрагенты",
     *                              "count": 7,
     *                              "status_id": 1,
     *                              "status_text": "Загружены",
     *                              "created_at": "2018-10-18T16:50:54+03:00",
     *                              "updated_at": "2018-10-19T09:12:42+03:00"
     *                          },
     *                          {
     *                              "id": 10,
     *                              "name": "store",
     *                              "title": "Склады",
     *                              "count": 10,
     *                              "status_id": 1,
     *                              "status_text": "Загружены",
     *                              "created_at": "2018-10-18T16:50:54+03:00",
     *                              "updated_at": "2018-10-19T09:12:42+03:00"
     *                          }
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
     * @throws BadRequestHttpException
     */
    public function actionList()
    {
        if (empty($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

        $this->response = (new Dictionary($this->request['service_id'], 'Dictionary'))->getList();
    }

    /**
     * @SWG\Post(path="/integration/dictionary/product-list",
     *     tags={"Integration/dictionary"},
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
     *                          "name": "название",
     *                          "business_id": "бизнес id"
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
     *                       "products": {
     *                          {
     *                            "id": 2763,
     *                            "name": "____сосиска2",
     *                            "unit": "кг",
     *                            "is_active": 1
     *                          },
     *                          {
     *                            "id": 2764,
     *                            "name": "А_Посольская об 0",
     *                            "unit": "кг",
     *                            "is_active": 1
     *                          },
     *                       },
     *                       "pagination": {
     *                            "page": 1,
     *                            "total_page": 17,
     *                            "page_size": 12
     *                       }
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
     * @throws BadRequestHttpException
     */
    public function actionProductList()
    {
        $this->response = (new Dictionary($this->request['service_id'], 'Product'))->productList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/agent-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список контрагентов",
     *     description="Список контрагентов",
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
     *                          "name": "название",
     *                          "business_id": "бизнес id"
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
     *                       "agents": {
     *                        {
     *                            "id": 1,
     *                            "outer_uid": "123",
     *                            "name": "name",
     *                            "vendor_id": 222,
     *                            "vendor_name": "TRAVEL COFFEE",
     *                            "store_id": 1,
     *                            "store_name": "qqqq",
     *                            "payment_delay": 5,
     *                            "is_active": 1,
     *                            "name_waybill": {
     *                                "huy",
     *                                "2huya"
     *                            }
     *                        },
     *                        {
     *                            "id": 2,
     *                            "outer_uid": "123",
     *                            "name": "name",
     *                            "vendor_id": 222,
     *                            "vendor_name": "TRAVEL COFFEE",
     *                            "store_id": 1,
     *                            "store_name": "qqqq",
     *                            "payment_delay": 5,
     *                            "is_active": 1,
     *                            "name_waybill": {}
     *                        },
     *                       },
     *                       "pagination": {
     *                            "page": 1,
     *                            "total_page": 17,
     *                            "page_size": 12
     *                       }
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
     * @throws BadRequestHttpException
     */
    public function actionAgentList()
    {
        if (empty($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

        $this->response = (new Dictionary($this->request['service_id'], 'Agent'))->agentList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/agent-update",
     *     tags={"Integration/dictionary"},
     *     summary="Обновление данных контрагента",
     *     description="Обновление данных контрагента",
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
     *                        "id": 1,
     *                        "service_id": 2,
     *                        "vendor_id": 3,
     *                        "store_id": 2,
     *                        "payment_delay": 5,
     *                        "name_waybill": {
     *                            "ООО Рос Прод Торг",
     *                            "Российская продовольственная торговая компания",
     *                            "ООО Российский продовльственный торговый дом"
     *                        }
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                            "id": 1,
     *                            "outer_uid": "123",
     *                            "name": "name",
     *                            "vendor_id": 222,
     *                            "vendor_name": "TRAVEL COFFEE",
     *                            "store_id": 1,
     *                            "store_name": "qqqq",
     *                            "payment_delay": 5,
     *                            "is_active": 1,
     *                            "name_waybill": {
     *                                "huy",
     *                                "2huya"
     *                            }
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
     * @throws BadRequestHttpException
     */
    public function actionAgentUpdate()
    {
        if (empty($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

        $this->response = (new Dictionary($this->request['service_id'], 'Agent'))->agentUpdate($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/store-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список складов",
     *     description="Полный список складов",
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
     *                          "name": "название",
     *                          "business_id": "бизнес id"
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
     *                  "stores": {
     *                      "id": 5,
     *                      "outer_uid": "c9319967c038f9b923068dabdf60cfe3",
     *                      "name": "Все склады",
     *                      "store_type": null,
     *                      "created_at": "2018-09-14T11:31:07-02:00",
     *                      "updated_at": null,
     *                      "is_active": 1,
     *                      "childs": {
     *                          {
     *                              "id": 9,
     *                              "outer_uid": "91e0dd93-0923-4509-9435-6cc6224768af",
     *                              "store_type": "STORE",
     *                              "created_at": "2018-09-14T11:31:07-02:00",
     *                              "updated_at": null,
     *                              "is_active": 1,
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 8,
     *                              "outer_uid": "73045059-5e4f-4358-90a4-23b2c0641e0f",
     *                              "name": "доп2 склад",
     *                              "store_type": "STORE",
     *                              "created_at": "2018-09-14T11:31:07-02:00",
     *                              "updated_at": null,
     *                              "is_active": 1,
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 7,
     *                              "outer_uid": "a3acc051-bfbb-45a9-9e1a-87d2f605f76e",
     *                              "name": "доп2 склад",
     *                              "store_type": "STORE",
     *                              "created_at": "2018-09-14T11:31:07-02:00",
     *                              "updated_at": null,
     *                              "is_active": 1,
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 6,
     *                              "outer_uid": "1239d270-1bbe-f64f-b7ea-5f00518ef508",
     *                              "name": "доп2 склад",
     *                              "store_type": "STORE",
     *                              "created_at": "2018-09-14T11:31:07-02:00",
     *                              "updated_at": null,
     *                              "is_active": 1,
     *                              "childs": {}
     *                          }
     *                      }
     *                  }
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
     * @throws BadRequestHttpException
     */

    public function actionStoreList()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        $this->response = (new Dictionary($this->request['service_id'], 'Store'))->storeList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/store-flat-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список складов (плоский)",
     *     description="Полный список складов (плоский)",
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
     *                          "name": "название",
     *                          "business_id": "бизнес id"
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
     *                  "stores": {
     *                      {
     *                          "id": 5,
     *                          "outer_uid": "c9319967c038f9b923068dabdf60cfe3",
     *                          "name": "Все склады",
     *                          "is_active": 1,
     *                          "is_category": true
     *                      },
     *                      {
     *                          "id": 5,
     *                          "outer_uid": "c9319967c038f9b923068dabdf60cfe3",
     *                          "name": "-Первый склад",
     *                          "is_active": 1,
     *                          "is_category": false
     *                      },
     *                      {
     *                          "id": 5,
     *                          "outer_uid": "c9319967c038f9b923068dabdf60cfe3",
     *                          "name": "--Второй склад",
     *                          "is_active": 1,
     *                          "is_category": false
     *                      }
     *                  }
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
     * @throws BadRequestHttpException
     */

    public function actionStoreFlatList()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        $this->response = (new Dictionary($this->request['service_id'], 'Store'))->storeFlatList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/unit-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список единиц измерения",
     *     description="Полный список единиц измерения",
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
     *                          "name": "наименование",
     *                          "business_id": "бизнес id"
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
     *                  "units": {
     *                      {
     *                          "id": 1,
     *                            "outer_uid": "1",
     *                            "ratio": 1,
     *                            "org_id": 3768,
     *                            "service_id": 1,
     *                            "name": "123",
     *                            "iso_code": null,
     *                            "is_deleted": null,
     *                            "created_at": null,
     *                            "updated_at": null
     *                      },
     *                      {
     *                          "id": 2,
     *                            "outer_uid": "2",
     *                            "ratio": 1,
     *                            "org_id": 3768,
     *                            "service_id": 1,
     *                            "name": "123",
     *                            "iso_code": null,
     *                            "is_deleted": null,
     *                            "created_at": null,
     *                            "updated_at": null
     *                      }
     *                  }
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
     * @throws BadRequestHttpException
     */

    public function actionUnitList()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        $this->response = (new Dictionary($this->request['service_id'], 'Unit'))->unitList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/category-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список категорий",
     *     description="Полный список категорий (для айко не доступно)",
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
     *                          "name": "наименование",
     *                          "business_id": "бизнес id"
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
     *                  "categories": {
     *                      "id": 5,
     *                      "outer_uid": "c9319967c038f9b923068dabdf60cfe3",
     *                      "name": "Каталог",
     *                      "selected": false,
     *                      "created_at": "2018-10-20T12:35:19+03:00",
     *                      "updated_at": "2018-10-26T16:13:24+03:00",
     *                      "childs": {
     *                          {
     *                              "id": 9,
     *                              "name": "Алкоголь",
     *                              "outer_uid": "91e0dd93-0923-4509-9435-6cc6224768af",
     *                              "selected": false,
     *                              "created_at": "2018-10-20T12:35:19+03:00",
     *                              "updated_at": "2018-10-26T16:13:24+03:00",
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 8,
     *                              "outer_uid": "73045059-5e4f-4358-90a4-23b2c0641e0f",
     *                              "name": "Алкоголь",
     *                              "selected": false,
     *                              "created_at": "2018-10-20T12:35:19+03:00",
     *                              "updated_at": "2018-10-26T16:13:24+03:00",
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 7,
     *                              "outer_uid": "a3acc051-bfbb-45a9-9e1a-87d2f605f76e",
     *                              "name": "Алкоголь",
     *                              "selected": false,
     *                              "created_at": "2018-10-20T12:35:19+03:00",
     *                              "updated_at": "2018-10-26T16:13:24+03:00",
     *                              "childs": {}
     *                          },
     *                          {
     *                              "id": 6,
     *                              "outer_uid": "1239d270-1bbe-f64f-b7ea-5f00518ef508",
     *                              "name": "Алкоголь",
     *                              "selected": false,
     *                              "created_at": "2018-10-20T12:35:19+03:00",
     *                              "updated_at": "2018-10-26T16:13:24+03:00",
     *                              "childs": {}
     *                          }
     *                      }
     *                  }
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
     * @throws BadRequestHttpException
     */

    public function actionCategoryList()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        $this->response = (new Dictionary($this->request['service_id'], 'Category'))->categoryList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/category-set-selected",
     *     tags={"Integration/dictionary"},
     *     summary="Выбор категории для загрузки номенклатуры",
     *     description="Используется для R-keeper",
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
     *                      "category_id": 123,
     *                      "selected": true,
     *                      "business_id": 1
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "selected": true
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
     * @throws BadRequestHttpException
     */

    public function actionCategorySetSelected()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        /** @var  $factory \api_web\modules\integration\classes\dictionaries\RkwsCategory*/
        $factory = (new Dictionary($this->request['service_id'], 'Category'));
        $this->response = $factory->categorySetSelected($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/check-agent-name",
     *     tags={"Integration/dictionary"},
     *     summary="Проверка: имеется ли такое название накладной",
     *     description="Проверяет существует ли данное название накладной в таблице",
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
     *                        "name": "ООО Рос Прод Торг"
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                          {
     *                              "result": false
     *                          },
     *                          {
     *                              "result": true
     *                          }
     *                      }
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
     * @throws BadRequestHttpException
     */
    public function actionCheckAgentName()
    {
        $this->response = Integration::checkAgentNameExists($this->request);
    }

}
