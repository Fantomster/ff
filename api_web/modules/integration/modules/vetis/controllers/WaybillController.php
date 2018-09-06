<?php

namespace api_web\modules\integration\modules\vetis\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\vetis\models\VetisWaybill;

class WaybillController extends WebApiController
{
    
    /**
     * @SWG\Post(path="/integration/vetis/waybill/list",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список сертификатов",
     *     description="Список сертификатов",
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
     *                  "search": {},
     *                  "pagination":{
     *                              "page": 1,
     *                              "page_size": 12
     *                          }
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": {
     *                            "id": 1
     *                      },
     *                      "pagination": {
     *                            "page": 1,
     *                            "total_page": 17,
     *                            "page_size": 12
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
    public function actionList()
    {
        $this->response = (new VetisWaybill())->getList($this->request);
    }
    
    /**
     * @SWG\Post(path="/integration/vetis/waybill/filter-sender",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список фильтров",
     *     description="Список фильтров по подрядчикам, если установлен search:sender_name ищет лайком по имени",
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
     *                  "search": {"sender_name":"часть имени"}
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": {
     *                          "a2667f4a-f91b-4752-b400-1bb129617de6": "Поставщик №1(600021, обл.Владимирская, г.Муром, ул.Октябрьской Революции,д.2Б)"
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
    public function actionFilterSender()
    {
        $this->response = (new VetisWaybill())->getSenderOrProductFilter($this->request, 'sender_name');
    }
    
    /**
     * @SWG\Post(path="/integration/vetis/waybill/filter-product",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список фильтров",
     *     description="Список фильтров по имени товара, если установлен search:product_name ищет лайком по имени",
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
     *                  "search": {"product_name":"часть имени"}
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": {
     *                          "краб камчатский живой": "краб камчатский живой",
     *                          "краб камчатский": "краб камчатский",
     *                          "краб": "краб"
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
    public function actionFilterProduct()
    {
        $this->response = (new VetisWaybill())->getSenderOrProductFilter($this->request, 'product_name');
    }
    
    /**
     * @SWG\Post(path="/integration/vetis/waybill/filter-status",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список фильтров",
     *     description="Список фильтров по статусу",
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
     *                      "result": {
     *                          "CONFIRMED": "Оформлен",
     *                          "WITHDRAWN": "Аннулирован",
     *                          "UTILIZED": "Погашен",
     *                          "": "Все"
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
    public function actionFilterStatus()
    {
        $this->response = (new VetisWaybill())->getFilterStatus();
    }
    
    /**
     * @SWG\Post(path="/integration/vetis/waybill/filter-vsd",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список фильтров",
     *     description="Список фильтров по ВСД",
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
     *                  "result": {
     *                      "INCOMING": "Входящий ВСД",
     *                      "OUTGOING": "Исходящий ВСД",
     *                      "": "Все ВСД"
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
     */
    public function actionFilterVsd()
    {
        $this->response = (new VetisWaybill())->getFilterVsd();
    }
    /**
     * @SWG\Post(path="/integration/vetis/waybill/filters",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список фильтров",
     *     description="Полный список фильтров",
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
     *                 "result": {
     *                     "vsd": {
     *                         "result": {
     *                             "INCOMING": "Входящий ВСД",
     *                             "OUTGOING": "Исходящий ВСД",
     *                             "all": "Все ВСД"
     *                         }
     *                     },
     *                     "statuses": {
     *                         "result": {
     *                             "CONFIRMED": "Оформлен",
     *                             "WITHDRAWN": "Аннулирован",
     *                             "UTILIZED": "Погашен",
     *                             "": "Все"
     *                         }
     *                     },
     *                     "sender": {
     *                         "result": {
     *                             "a2667f4a-f91b-4752-b400-1bb129617de6": "Поставщик №1(600021, обл.Владимирская, г.Муром, ул.Октябрьской Революции,д.2Б)",
     *                            "f8805c8f-1da4-4bda-aaca-a08b5d1cab1b": "Поставщик №2(600021, обл.Владимирская, г.Муром, ул.Октябрьской Революции,д.2Б)"
     *                         }
     *                     },
     *                     "product": {
     *                         "result": {
     *                             "козлятина охлажденная": "козлятина охлажденная",
     *                             "говядина КРУТЕЙШАЯ": "говядина КРУТЕЙШАЯ"
     *                         }
     *                     }
     *                 }
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
    public function actionFilters()
    {
        $this->response = (new VetisWaybill())->getFilters();
    }

    /**
     * @SWG\Post(path="/integration/vetis/waybill/short-info-vsd",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Краткая информация о ВСД",
     *     description="Краткая информация о ВСД",
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
     *                      "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                        "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                        "country_name": "Россия",
     *                        "producer_name":"ООО Мираторг, 600021, Владимирская обл., г. Муром, ул. Октябрьской революции 16",
     *                        "referenced_document":"3345 231234",
     *                        "referenced_date":"23.04.1025",
     *                        "cargo_expertized":"Положительный результат.",
     *                        "location_prosperity":"Благополучна",
     *                        "special_marks":"Особые отметки, любой текст",
     *                        "vehicle_number":"a666sf777tiv"
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
     */
    public function actionShortInfoVsd()
    {
        $this->response = (new VetisWaybill())->getShortInfoAboutVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/waybill/full-info-vsd",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Полная информация о ВСД",
     *     description="Полная информация о ВСД",
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
     *                      "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                      "org_id": 3768,
     *                      "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                      "producer_name": "Рыбкин Дом ИП Понасков А.А.(ст. Пшехская ул. Красная б/н)",
     *                      "country_name": "Российская Федерация",
     *                      "cargo_expertized": "Продукция подвергнута ВСЭ в полном объеме",
     *                      "location_prosperity": "Местность благополучна по заразным болезням животных",
     *                      "specialMarks": "",
     *                      "vehicle_number": "54258",
     *                      "consignor_business": null,
     *                      "product_type": "Рыба и морепродукты",
     *                      "product": "живая рыба лососевых пород",
     *                      "sub_product": "коралловая форель",
     *                      "product_in_numenclature": "коралловая форель",
     *                      "volume": "50.0 кг",
     *                      "date_of_production": "2018-5-17 0:00:00",
     *                      "expiry_date_of_production": "Смерти рыбы",
     *                      "perishable_products": "Да",
     *                      "producers": "",
     *                      "expertiseInfo": "ЭмпаерЛАБ эксп №1224 от 2018-05-17 ( Наличие паразитов - Паразиты отстутствуют )",
     *                      "transport_type": "Автомобильный",
     *                      "transport_number": "54258",
     *                      "transport_storage_type": "Охлаждаемый",
     *                      "specified_person": "Понитков Максим Алексеевич",
     *                      "specified_person_post": "Project Manager"
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
     */
    public function actionFullInfoVsd()
    {
        $this->response = (new VetisWaybill())->getFullInfoAboutVsd($this->request);
    }
}