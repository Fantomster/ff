<?php

namespace api_web\modules\integration\modules\egais\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\egais\models\EgaisMethods;

/**
 * Class DefaultController
 *
 * @package api_web\modules\integration\modules\egais\controllers
 */
class DefaultController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/egais/set-egais-settings",
     *     tags={"Integration/egais"},
     *     summary="Настройки ЕГАИС",
     *     description="Задаём настройки для ЕГАИС",
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
     *                    "egais_url": "http://192.168.1.70:8090",
     *                    "fsrar_id": "030000443640",
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": true
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
     * @throws \Exception
     */
    public function actionSetEgaisSettings()
    {
        $this->response = (new EgaisMethods())->setEgaisSettings($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/write-off-types",
     *     tags={"Integration/egais"},
     *     summary="ЕГАИС запрос типов списания",
     *     description="запрашиваем список типов списания",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={}
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  {
     *                      "id": 1,
     *                      "type": "Пересортица"
     *                  },
     *                  {
     *                      "id": 2,
     *                      "type": "Недостача"
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
     * @throws \Exception
     */
    public function actionWriteOffTypes()
    {
        return $this->response = (new EgaisMethods())->getWriteOffTypes();
    }

    /**
     * @SWG\Post(path="/integration/egais/list-goods-balance",
     *     tags={"Integration/egais"},
     *     summary="ЕГАИС получение списка товаров на балансе организации",
     *     description="запрашиваем список товаров на балансе организации",
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
     *                      "org_id" : 1
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  {
     *                      "id": 249,
     *                      "org_id": 3768,
     *                      "quantity": "9.0000",
     *                      "inform_a_reg_id": "TEST-FA-000000036386254",
     *                      "inform_b_reg_id": "TEST-FB-000000036818449",
     *                      "full_name": "Водка 'Журавли'",
     *                      "alc_code": "0150325000001195171",
     *                      "capacity": "0.7000",
     *                      "alc_volume": "40.000",
     *                      "product_v_code": 200,
     *                      "producer_client_reg_id": "010000000467",
     *                      "producer_inn": "5038002790",
     *                      "producer_kpp": "503801001",
     *                      "producer_full_name": "Акционерное общество 'Ликеро-водочный завод 'Топаз'",
     *                      "producer_short_name": "АО 'ЛВЗ' 'Топаз'",
     *                      "address_country": 643,
     *                      "address_region_code": 50,
     *                      "address_description": "РОССИЯ,,МОСКОВСКАЯ ОБЛ,,Пушкино г,,Октябрьская ул, д. 46,, | (за
     *                      исключением литера Б, 1 этаж, № на плане 8)"
     *                  },
     *                  {
     *                      "id": 250,
     *                      "org_id": 3768,
     *                      "quantity": "5.0000",
     *                      "inform_a_reg_id": "TEST-FA-000000036386259",
     *                      "inform_b_reg_id": "TEST-FB-000000036818469",
     *                      "full_name": "Водка 'ЦАРСКАЯ ЧАРКА ЗОЛОТАЯ'",
     *                      "alc_code": "0116118000000008004",
     *                      "capacity": "1.0000",
     *                      "alc_volume": "40.000",
     *                      "product_v_code": 200,
     *                      "producer_client_reg_id": "010000000134",
     *                      "producer_inn": "1681000049",
     *                      "producer_kpp": "165102001",
     *                      "producer_full_name": "Акционерное общество 'Татспиртпром'",
     *                      "producer_short_name": "АО 'Татспиртпром'",
     *                      "address_country": 643,
     *                      "address_region_code": 16,
     *                      "address_description": "РОССИЯ,,ТАТАРСТАН РЕСП,Нижнекамский муниципальный район,Нижнекамск
     *                      г,,Чистопольская ул, д. 45,,"
     *                  }
     *              }
     *          )
     *     ),
     * @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionListGoodsBalance()
    {
        return $this->response = (new EgaisMethods())->getGoodsOnBalance($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/act-write-off",
     *     tags={"Integration/egais"},
     *     summary="Акт списания",
     *     description="Акт списания ЕГАИС",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(property="request", ref="#/definitions/ActWriteOffV3"),
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": true
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
     * @throws \Exception
     */
    public function actionActWriteOff()
    {
        $this->response = (new EgaisMethods())->actWriteOff($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/act-write-on",
     *     tags={"Integration/egais"},
     *     summary="Акт постановки",
     *     description="Акт постановки на баланс",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(property="request", ref="#/definitions/ActChargeOnV2"),
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": true
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
     * @throws \Exception
     */
    public function actionActWriteOn()
    {
        $this->response = (new EgaisMethods())->actWriteOn($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/all-incoming-doc",
     *     tags={"Integration/egais"},
     *     summary="ЕГАИС запрос документов",
     *     description="запрашиваем список всех входящих документов в ЕГАИС",
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
     *                      "type": "ticket",
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
     *                  "documents" : {
     *                       {
     *                         "field": "020x4E794272-40D1-42E1-AE42-8A5EB329D782",
     *                         "replyId" : "de1b394d-50fd-45ce-b1b4-3e8d164798b1",
     *                         "timestamp": "2018-10-18T17:04:14.505+0300",
     *                         "type": "Ticket",
     *                         "id": 227
     *                       }
     *                   },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 7
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
     * @throws \Exception
     */
    public function actionAllIncomingDoc()
    {
        return $this->response = (new EgaisMethods())->getAllIncomingDoc($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/one-incoming-doc",
     *     tags={"Integration/egais"},
     *     summary="ЕГАИС запрос одного документа",
     *     description="запрашиваем один входящий документ в ЕГАИС",
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
     *                      "org_id":1,
     *                      "type":"ticket",
     *                      "id": 275,
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": "В зависимости от типа документа получится разный результат."
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
     * @throws \Exception
     */
    public function actionOneIncomingDoc()
    {
        return $this->response = (new EgaisMethods())->getOneIncomingDoc($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/product-balance-info",
     *     tags={"Integration/egais"},
     *     summary="Получение конкретной позиции из остатков",
     *     description="Получение конкретной позиции из остатков в ЕГАИС по alc_code",
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
     *                      "alc_code":0150325000001195171,
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "id": 13,
     *                  "org_id": 3768,
     *                  "quantity": "1.0000",
     *                  "inform_a_reg_id": "TEST-FA-000000016790162",
     *                  "inform_b_reg_id": "TEST-FB-000000036818404",
     *                  "full_name": "Водка 'Журавли'",
     *                  "alc_code": "0150325000001195171",
     *                  "capacity": null,
     *                  "alc_volume": null,
     *                  "product_v_code": 200,
     *                  "producer_client_reg_id": "010000000467",
     *                  "producer_inn": "5038002790",
     *                  "producer_kpp": "503801001",
     *                  "producer_full_name": "Акционерное общество 'Ликеро-водочный завод Топаз'",
     *                  "producer_short_name": "АО 'ЛВЗ Топаз'",
     *                  "address_country": 643,
     *                  "address_region_code": 50,
     *                  "address_description": "РОССИЯ,,МОСКОВСКАЯ ОБЛ,,Пушкино г,,Октябрьская ул, д. 46,, | (за исключением литера Б, 1 этаж, № на плане 8)"
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
     * @throws \Exception
     */
    public function actionProductBalanceInfo()
    {
        return $this->response = (new EgaisMethods())->getProductBalanceInfo($this->request);
    }

    /**
     * @SWG\Post(path="/integration/egais/written-off-product-list",
     *     tags={"Integration/egais"},
     *     summary="Cписок списанной продукции",
     *     description="Cписок продукции списанной за определенный промежуток времени",
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
     *                      "date": {
     *                          "start": "10.02.2019",
     *                          "end": "15.02.2019"
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size":12
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
     *                  "items": {
     *                    {
     *                      "id": 7,
     *                      "organization": {
     *                        "id": 3768,
     *                        "name": "капотник"
     *                      },
     *                      "act": {
     *                        "id": 5,
     *                        "org_id": 3768,
     *                        "identity": 45,
     *                        "act_number": 131,
     *                        "act_date": "2018-12-11",
     *                        "type_write_off": 9,
     *                        "note": "текст комментария",
     *                        "status": null,
     *                        "created_at": "2018-12-11 10:43:42",
     *                        "updated_at": null
     *                      },
     *                      "product": {
     *                        "id": 17,
     *                        "org_id": 3768,
     *                        "quantity": "2.0000",
     *                        "inform_a_reg_id": "TEST-FA-000000036388068",
     *                        "inform_b_reg_id": "TEST-FB-000000036833202",
     *                        "full_name": "Водка 'Журавли'",
     *                        "alc_code": "0150325000001195171",
     *                        "capacity": null,
     *                        "alc_volume": null,
     *                        "product_v_code": 200,
     *                        "producer_client_reg_id": "010000000467",
     *                        "producer_inn": "5038002790",
     *                        "producer_kpp": "503801001",
     *                        "producer_full_name": "Акционерное общество 'Ликеро-водочный завод Топаз'",
     *                        "producer_short_name": "АО 'ЛВЗ Топаз'",
     *                        "address_country": 643,
     *                        "address_region_code": 50,
     *                        "address_description": "РОССИЯ,,МОСКОВСКАЯ ОБЛ,,Пушкино г,,Октябрьская ул, д. 46,, | (за исключением литера Б, 1 этаж, № на плане 8)"
     *                      },
     *                      "type_write_off": {
     *                        "id": 5,
     *                        "name": "Потери"
     *                      },
     *                      "status": null,
     *                      "created_at": "2019-02-15 12:12:29",
     *                      "updated_at": "2019-02-15 12:12:29"
     *                    }
     *                  },
     *                  "pagination": {
     *                    "page": 1,
     *                    "page_size": 1,
     *                    "total_page": 12
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
     * @throws \Exception
     */
    public function actionWrittenOffProductList()
    {
        return $this->response = (new EgaisMethods())->getWrittenOffProductList($this->request);
    }
}