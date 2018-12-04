<?php

namespace api_web\modules\integration\modules\vetis\controllers;

use api_web\components\Registry;
use api_web\components\WebApiController;
use api_web\modules\integration\modules\vetis\models\VetisWaybill;

/**
 * Class DefaultController
 *
 * @package api_web\modules\integration\modules\vetis\controllers
 */
class DefaultController extends WebApiController
{
    /**
     * Для контроллера нужна лицензия меркурия
     *
     * @var int
     */
    public $license_service_id = Registry::MERC_SERVICE_ID;

    /**
     * @SWG\Post(path="/integration/vetis/groups-list",
     *     tags={"Integration/vetis"},
     *     summary="Список групп и сертификатов",
     *     description="Список групп и сертификатов",
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
     *                          "product_name": {"мясо ягненка"},
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
     *                            "items": {
     *                                  {
     *                                      "uuid": "774daf95-01ee-486c-ae05-4ab301a9b85d",
     *                                      "sender_name": "Поставщик №1(600021, обл.Владимирская, г.Муром, ул.Октябрьской Революции,д.2Б)",
     *                                      "product_name": "мясо верблюда",
     *                                      "status": "CONFIRMED",
     *                                      "status_date": "2018-08-30T13:11:02+03:00",
     *                                      "amount": "55.000",
     *                                      "unit": "кг",
     *                                      "production_date": "2018-07-02T03:00:00+03:00",
     *                                      "date_doc": "2018-08-30T15:00:00+03:00",
     *                                      "document_id": null,
     *                                      "status_text": "Статус"
     *                                  },
     *                              },
     *                              "groups": {
     *                                  "6776": {
     *                                      "count": "3",
     *                                      "created_at": "2018-09-04T09:55:22+03:00",
     *                                      "total_price": "30.00",
     *                                      "vendor_name": "EL Поставщик",
     *                                      "statuses": {
     *                                          "id": "CONFIRMED",
     *                                          "text": "Сертификаты ожидают погашения"
     *                                      }
     *                                  }
     *                              }
     *                      },
     *                      "pagination": {
     *                            "page": 1,
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
     * @throws \Exception
     */
    public function actionGroupsList()
    {
        $this->response = (new VetisWaybill())->getGroupsList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/list",
     *     tags={"Integration/vetis"},
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
     * @SWG\Post(path="/integration/vetis/filter-sender",
     *     tags={"Integration/vetis"},
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
     *                  "search": {"sender_name":"часть имени", "acquirer_id": 7}
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
     * @SWG\Post(path="/integration/vetis/filter-product",
     *     tags={"Integration/vetis"},
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
     *                  "search": {"product_name":"часть имени", "acquirer_id": 7}
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
     * @SWG\Post(path="/integration/vetis/filter-status",
     *     tags={"Integration/vetis"},
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
     * @SWG\Post(path="/integration/vetis/filter-vsd",
     *     tags={"Integration/vetis"},
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
     * @SWG\Post(path="/integration/vetis/filters",
     *     tags={"Integration/vetis"},
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
     * @SWG\Post(path="/integration/vetis/short-info-vsd",
     *     tags={"Integration/vetis"},
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
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionShortInfoVsd()
    {
        $this->response = (new VetisWaybill())->getShortInfoAboutVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/full-info-vsd",
     *     tags={"Integration/vetis"},
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
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFullInfoVsd()
    {
        $this->response = (new VetisWaybill())->getFullInfoAboutVsd($this->request);
    }


    /**
     * @SWG\Post(path="/integration/vetis/return-vsd",
     *     tags={"Integration/vetis"},
     *     summary="Возврат ВСД",
     *     description="Возврат ВСД",
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
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionReturnVsd()
    {
        $this->response = (new VetisWaybill())->returnVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/partial-acceptance",
     *     tags={"Integration/vetis"},
     *     summary="Частичный возврат ВСД",
     *     description="Частичный возврат ВСД",
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
     *                      "amount":"40",
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
     * @throws \Exception
     */
    public function actionPartialAcceptance()
    {
        $this->response = (new VetisWaybill())->partialAcceptance($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/repay-vsd",
     *     tags={"Integration/vetis"},
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
     * @throws \Exception
     */
    public function actionRepayVsd()
    {
        $this->response = (new VetisWaybill())->repayVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/acquirer-filter",
     *     tags={"Integration/vetis"},
     *     summary="Список фильтров имен бизнесов",
     *     description="Список доступных бизнесов для юзеров",
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
     *
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
     *                      {
     *                          "id": "4300",
     *                          "parent_id": "4398",
     *                          "name": "1йцу"
     *                      },
     *                      {
     *                          "id": "4392",
     *                          "parent_id": "4398",
     *                          "name": "тест сортировка"
     *                      },
     *                      {
     *                          "id": "4400",
     *                          "parent_id": "4398",
     *                          "name": "421"
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
     * @throws \Exception
     */
    public function actionAcquirerFilter()
    {
        $this->response = $this->container->get('UserWebApi')->getUserOrganizationBusinessList();
    }

    /**
     * @SWG\Post(path="/integration/vetis/get-not-confirmed-vsd",
     *     tags={"Integration/vetis"},
     *     summary="Список непогашенных ВСД",
     *     description="Список непогашенных ВСД",
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
     *                      "org_id": "1234"
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
     *                      "uuids": {
     *                          "6e8e9f51-69f8-4c9c-9117-88d5698eb641",
     *                          "e9d0b1ef-d70a-403c-a76b-0b330c1556d6",
     *                          "0207005d-2bad-43d9-af69-fc8a54470114",
     *                          "73dd6fc8-7d07-4e4c-ac21-4707f3611512",
     *                          "08a8b8ed-0e42-42fd-8528-49d6c215f446",
     *                          "c79b0223-9136-417f-b41b-a251b01b483f",
     *                          "6c1e09c9-a109-4e86-90b2-4128b47a17d9",
     *                          "cda6ac61-f5c9-4783-a257-a674463f57c6",
     *                          "89558ffc-887a-4d5c-a153-98939855993c",
     *                          "72aac45f-b082-477d-9d36-ff108ae327b9",
     *                          "4914f8ff-0c85-494b-a3a5-614509f4e21d",
     *                          "18eb0b57-82eb-4738-83ff-2ee120be4f8a",
     *                          "6a781eb8-c314-4026-b40c-02fd80f12e57"
     *                          },
     *                      "count": "13"
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
    public function actionGetNotConfirmedVsd()
    {
        $this->response = (new VetisWaybill())->getNotConfirmedVsd($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/get-vsd-pdf",
     *     tags={"Integration/vetis"},
     *     summary="Получить ВСД в PDF",
     *     description="Получить ВСД в PDF",
     *     produces={"application/json", "application/pdf"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                      "full": 1,
     *                      "base64_encode": 1
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description="Если все прошло хорошо вернет файл закодированый в base64",
     *         @SWG\Schema(
     *              default="JVBERi0xLjQKJeLjz9MKMyAwIG9iago8PC9UeXBlIC9QYWdlCi9QYXJlbnQgMSAwIFIKL01lZGlhQm94IFswIDAgNTk1LjI4MCA4NDEuOD"
     *         )
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
    public function actionGetVsdPdf()
    {
        $result = (new VetisWaybill())->getVsdPdf($this->request);
        if (is_array($result)) {
            $this->response = $result;
        } else {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers:Content-Type, Authorization');
            header('Content-Disposition:attachment; filename=vsd_' . $this->request['uuid'] . '.pdf');
            header("Content-type:application/pdf");
            exit($result);
        }
    }
}