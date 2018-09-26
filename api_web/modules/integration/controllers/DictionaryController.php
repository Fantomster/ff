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
     * @SWG\Post(path="/integration/dictionary/product-list",
     *     tags={"Integration/dictionary/product"},
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
        if (empty($this->request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

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
     *                          "name": "название"
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
     */

    public function actionStoreList()
    {
        $this->response = (new Dictionary($this->request['service_id'], 'Store'))->storeList($this->request);
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
     *                        "agent_id": 1,
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
     *                              "result": false,
     *                              "message": "Такое название уже задано"
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
