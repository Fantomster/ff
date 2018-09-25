<?php

namespace api_web\modules\integration\modules\vetis\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\vetis\models\VetisWaybill;

class WaybillController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/vetis/waybill/groups-list",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Список групп сертификатов",
     *     description="Список групп сертификатов",
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
     *                    "search": {
     *                          "acquirer_id": 1,
     *                          "type": "INCOMING",
     *                          "status": "CONFIRMED",
     *                          "sender_guid": {"f8805c8f-1da4-4bda-aaca-a08b5d1cab1b"},
     *                          "product_name": "мясо ягненка",
     *                          "date":{
     *                              "from":"22.22.1111",
     *                              "to":"22.22.1111"
     *                          }
     *                      },
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
     *                      "result": {
     *                            "documents": {
     *                                  "6777": {
     *                                      "count": 1,
     *                                      "date": "2018-09-04T10:08:18+03:00",
     *                                      "vendor_name": "EL Поставщик",
     *                                      "sender_name": "Поставщик №2(600021, обл.Владимирская, г.Муром, ул.Октябрьской Революции,д.2Б)",
     *                                      "total_price": "76.88",
     *                                      "uuids": {
     *                                          "d50becf5-ad90-45dd-aebd-8bc36fe984e0"
     *                                      },
     *                                      "status": {
     *                                          "id": "UTILIZED",
     *                                          "text": "Сертификаты погашены"
     *                                      }
     *                                  }
     *                              },
     *                              "order_not_installed": {
     *                                  "uuids": {
     *                                      "df618d56-67c5-4c89-8956-45f54ff7ebfd",
     *                                      "6a781eb8-c314-4026-b40c-02fd80f12e57",
     *                                      "1d7befcb-57c4-44fd-adc9-f058972739f9",
     *                                      "1495e3f9-35ab-46e4-aeff-618921e4e168"
     *                                  }
     *                              }
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
    public function actionGroupsList()
    {
        $this->response = (new VetisWaybill())->getGroupsList($this->request);
    }

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
     *                    "uuids": {
     *                      "d50becf5-ad90-45dd-aebd-8bc36fe984e0",
     *                      "80679a18-d03d-45e0-8d0f-adf8f09ec77e",
     *                      "e3ec3a8d-ecc0-4267-8e65-559bde8d663f",
     *                      "db3ba021-5165-4d6b-98ce-af8c621731a6"
     *                    }
     *                 }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      "result": {
     *                            {
     *                                "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                                "product_name": "Говядина бескостная рубленая БИО, Замороженная",
     *                                "sender_name": "Поставщик №1 (600021, Владимерская обл., г. Муром, ул. Октяборьской революции 16",
     *                                "status": "CONFIRMED",
     *                                "status_text": "Оформлен",
     *                                "status_date": "29.08.2018",
     *                                "amount": 40,
     *                                "unit": "кг",
     *                                "production_date": "29.08.2018",
     *                                "date_doc": "29.08.2018"
     *                                },
     *                                {
     *                                "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                                "product_name": "Говядина бескостная рубленая БИО, Замороженная",
     *                                "sender_name": "Поставщик №1 (600021, Владимерская обл., г. Муром, ул. Октяборьской революции 16",
     *                                "status": "CONFIRMED",
     *                                "status_text": "Оформлен",
     *                                "status_date": "29.08.2018",
     *                                "amount": 40,
     *                                "unit": "кг",
     *                                "production_date": "29.08.2018",
     *                                "date_doc": "29.08.2018"
     *                                }
     *                           }
     *                      }
     *              )
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

    /**
     * @SWG\Post(path="/integration/vetis/waybill/repay-vsd",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Погашение ВСД",
     *     description="Погашение ВСД",
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
     *                      "uuids": {
     *                                 "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                                  "eb9eed88-919d-422d-9593-8092fdb91ab7",
     *                                  "470b17ea-9e16-434d-b3d6-b3064324ca82"
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
     *                  "result": {
     *                      "ede52e76-6091-46bb-9349-87324ee1ae41":true,
     *                      "eb9eed88-919d-422d-9593-8092fdb91ab7":false,
     *                      "470b17ea-9e16-434d-b3d6-b3064324ca82":true
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
    public function actionRepayVsd()
    {
        $this->response = (new VetisWaybill())->repayVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/waybill/partial-acceptance",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Частичное погашение ВСД",
     *     description="Частичное погашение ВСД amount: 37, Не может быть больше merc_vsd.amount reason:Частичная приемка, Обязательное поле",
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
     *                      "uuid": "93cdc45a-edc3-472f-bd70-99ffca18edc9",
     *                      "amount": 37,
     *                      "reason":"Частичная приемка",
     *                      "description":"long string description"
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
     *                      "ede52e76-6091-46bb-9349-87324ee1ae41":true,
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
    public function actionPartialAcceptance()
    {
        $this->response = (new VetisWaybill())->partialAcceptance($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/waybill/return-vsd",
     *     tags={"Integration/vetis/waybill"},
     *     summary="Частичное погашение ВСД",
     *     description="Частичное погашение ВСД amount: 37, Не может быть больше merc_vsd.amount reason:Частичная приемка, Обязательное поле",
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
     *                      "uuid": "93cdc45a-edc3-472f-bd70-99ffca18edc9",
     *                      "reason":"Nulla in gravida ex. In hac habitasse platea dictumst.",
     *                      "description":"long string description"
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
     *                      "ede52e76-6091-46bb-9349-87324ee1ae41":true,
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
    public function actionReturnVsd()
    {
        $this->response = (new VetisWaybill())->returnVsd($this->request);
    }
}