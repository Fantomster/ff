<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:52 AM
 */

namespace api_web\modules\integration\controllers;

use api_web\components\Registry;
use api_web\modules\integration\classes\dictionaries\AbstractDictionary;
use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\classes\Integration;
use api_web\modules\integration\modules\vetis\models\VetisWaybill;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы со Справочниками
 * Class DictionaryController
 *
 * @package api_web\modules\integration\controllers
 */
class DictionaryController extends \api_web\components\WebApiController
{
    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     */
    public function beforeAction($action)
    {
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        return parent::beforeAction($action);
    }

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
     *                              "upload": true,
     *                              "prefix": "Rkws",
     *                              "created_at": "2018-10-18T16:50:54+03:00",
     *                              "updated_at": "2018-10-19T09:12:42+03:00"
     *                          },
     *                          {
     *                              "id": 10,
     *                              "name": "store",
     *                              "title": "Склады",
     *                              "count": 10,
     *                              "status_id": 1,
     *                              "upload": true,
     *                              "prefix": "Rkws",
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

        /** @var AbstractDictionary $dictionary */
        $dictionary = (new Dictionary($this->request['service_id'], 'Dictionary'));

        $this->response = $dictionary->getList();
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
     * @throws \Exception
     */
    public function actionProductList()
    {
        /** @var AbstractDictionary $dictionary */
        $dictionary = (new Dictionary($this->request['service_id'], 'Product'));

        $this->response = $dictionary->productList($this->request);
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
     * @throws \Exception
     */
    public function actionAgentList()
    {
        if (empty($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

        /** @var AbstractDictionary $dictionary */
        $dictionary = (new Dictionary($this->request['service_id'], 'Agent'));

        $this->response = $dictionary->agentList($this->request);
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
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionCategorySetSelected()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        /** @var  $factory \api_web\modules\integration\classes\dictionaries\AbstractDictionary */
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
     * @throws \yii\db\Exception
     */
    public function actionCheckAgentName()
    {
        $this->response = Integration::checkAgentNameExists($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/product-type-list",
     *     tags={"Integration/dictionary"},
     *     summary="Список типов продуктов",
     *     description="Полный список типов продуктов",
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
     *                          "business_id": 1
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
     *                  "product_types": {
     *                      {
     *                          "product_type_id": 11,
     *                          "comment": "Товар",
     *                          "selected": true
     *                      },
     *                      {
     *                          "product_type_id": 12,
     *                          "comment": "Блюдо",
     *                          "selected": false
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
    public function actionProductTypeList()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        $this->response = (new Dictionary($this->request['service_id'], 'ProductType'))->productTypeList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/product-type-set-selected",
     *     tags={"Integration/dictionary"},
     *     summary="Выбор типа продукта для загрузки номенклатуры",
     *     description="Используется для iiko",
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
     *                      "product_type_id": 123,
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
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionProductTypeSetSelected()
    {
        if (!isset($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }
        /** @var \api_web\modules\integration\classes\dictionaries\AbstractDictionary $factory */
        $factory = (new Dictionary($this->request['service_id'], 'ProductType'));
        $this->response = $factory->productTypeSetSelected($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/vetis-business-entity",
     *     tags={"Integration/dictionary"},
     *     summary="Словарь Хозяйствующие субъекты",
     *     description="Словарь Хозяйствующие субъекты",
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
     *                  {
     *                      "name": "СЕЛЬСКОХОЗЯЙСТВЕННЫЙ ПРОИЗВОДСТВЕННЫЙ КООПЕРАТИВ ПЛЕМЗАВОД КОЛХОЗ ИМЕНИ КИРОВА",
     *                      "fullname": "СЕЛЬСКОХОЗЯЙСТВЕННЫЙ ПРОИЗВОДСТВЕННЫЙ КООПЕРАТИВ ПЛЕМЗАВОД КОЛХОЗ ИМЕНИ
     *                      КИРОВА",
     *                      "uuid": "00000c3b-48d1-40bf-ba74-4899f99d032a",
     *                      "guid": "0605cda7-e107-49af-abfe-970714f99849",
     *                      "inn": null,
     *                      "address": "Российская Федерация, г. Москва, Осенний б-р",
     *                      "active": 1
     *                  },
     *                  {
     *                      "name": "Тулунский психоневрологический интернат",
     *                      "fullname": "Тулунский психоневрологический интернат",
     *                      "uuid": "0000331b-68ec-42a7-9ef6-b1715af275fa",
     *                      "guid": "58b751b5-d236-4c97-9f3c-fa21268e7f21",
     *                      "inn": null,
     *                      "address": "353795 ст.Андреевская ул.Красная д. 12",
     *                      "active": 1
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
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionVetisBusinessEntity()
    {
        /** @var \api_web\modules\integration\classes\dictionaries\MercDictionary $factory */
        $factory = (new Dictionary(Registry::MERC_SERVICE_ID, 'Dictionary'));
        $this->response = $factory->getBusinessEntityList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/vetis-russian-enterprise",
     *     tags={"Integration/dictionary"},
     *     summary="Словарь Отечественные предприятия",
     *     description="Словарь Отечественные предприятия",
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
     *                  {
     *                      "name": "ИП Рогов Андрей Сергеевич",
     *                      "uuid": "00000c3b-48d1-40bf-ba74-4899f99d032a",
     *                      "guid": "0605cda7-e107-49af-abfe-970714f99849",
     *                      "inn": null,
     *                      "address": "Российская Федерация, г. Москва, Осенний б-р",
     *                      "active": 1
     *                  },
     *                  {
     *                      "name": "Пудожское райпо",
     *                      "uuid": "0000331b-68ec-42a7-9ef6-b1715af275fa",
     *                      "guid": "58b751b5-d236-4c97-9f3c-fa21268e7f21",
     *                      "inn": null,
     *                      "address": "353795 ст.Андреевская ул.Красная д. 12",
     *                      "active": 1
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
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionVetisRussianEnterprise()
    {
        /** @var \api_web\modules\integration\classes\dictionaries\MercDictionary $factory */
        $factory = (new Dictionary(Registry::MERC_SERVICE_ID, 'Dictionary'));
        $this->response = $factory->getRussianEnterpriseList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/vetis-foreign-enterprise",
     *     tags={"Integration/dictionary"},
     *     summary="Словарь Отечественные предприятия",
     *     description="Словарь Отечественные предприятия",
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
     *                  "result": {
     *                      {
     *                          "name": "Curvaceiras",
     *                          "uuid": "00000c3b-48d1-40bf-ba74-4899f99d032a",
     *                          "guid": "0605cda7-e107-49af-abfe-970714f99849",
     *                          "inn": null,
     *                          "address": "Estrada da Lamarosa, Paialvo - Tomar",
     *                          "active": 1
     *                      },
     *                      {
     *                          "name": "ФОП Гастов И.В.",
     *                          "uuid": "0000331b-68ec-42a7-9ef6-b1715af275fa",
     *                          "guid": "58b751b5-d236-4c97-9f3c-fa21268e7f21",
     *                          "inn": null,
     *                          "address": "Киевская область, пгт Ворзель, ул.Ворошилова 44",
     *                          "active": 1
     *                      }
     *                  },
     *                  "pagination": {
     *                       "page": 1,
     *                       "total_page": 17,
     *                       "page_size": 12
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
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionVetisForeignEnterprise()
    {
        /** @var \api_web\modules\integration\classes\dictionaries\MercDictionary $factory */
        $factory = (new Dictionary(Registry::MERC_SERVICE_ID, 'Dictionary'));
        $this->response = $factory->getForeignEnterpriseList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/vetis-transport",
     *     tags={"Integration/dictionary"},
     *     summary="Словарь Транспортных средств",
     *     description="Словарь Транспортных средств",
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
     *                      "org_id": 1,
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
     *                  "result": {
     *                      {
     *                          "vehicle_number": "номер машины",
     *                          "trailer_number": "номер полуприцепа",
     *                          "container_number": "номер контейнера",
     *                          "transport_storage_type": "способ хранения",
     *                          "id": 1
     *                      },
     *                      {
     *                          "vehicle_number": "номер машины",
     *                          "trailer_number": "номер полуприцепа",
     *                          "container_number": "номер контейнера",
     *                          "transport_storage_type": "способ хранения",
     *                          "id": 2
     *                      }
     *                  },
     *                  "pagination": {
     *                       "page": 1,
     *                       "total_page": 17,
     *                       "page_size": 12
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
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionVetisTransport()
    {
        /** @var \api_web\modules\integration\classes\dictionaries\MercDictionary $factory */
        $factory = (new Dictionary(Registry::MERC_SERVICE_ID, 'Dictionary'));
        $this->response = $factory->getTransportList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/dictionary/vetis-product-item",
     *     tags={"Integration/dictionary"},
     *     summary="Словарь Отечественные предприятия",
     *     description="Словарь Отечественные предприятия",
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
     *                  "result": {
     *                      {
     *                          "name": "Сельдь ф/к с луком",
     *                          "uuid": "00f4334f-23d5-468b-81c7-258f097bab0e",
     *                          "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                          "form": "Рыба и морепродукты",
     *                          "article": "null",
     *                          "gtin": "null",
     *                          "gost": "",
     *                          "active": 1
     *                      },
     *                      {
     *                          "name": "Шашлык",
     *                          "uuid": "00f4682b-b09b-42ef-8773-6a4beea42680",
     *                          "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
     *                          "form": "Пищевые продукты",
     *                          "article": "1134",
     *                          "gtin": "null",
     *                          "gost": "null",
     *                          "active": 1
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
     * @throws \yii\base\InvalidArgumentException
     */
    public function actionVetisProductItem()
    {
        $this->response = (new VetisWaybill())->getProductItemList($this->request);
    }
}
