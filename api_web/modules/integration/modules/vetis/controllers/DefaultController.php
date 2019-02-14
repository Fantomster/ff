<?php

namespace api_web\modules\integration\modules\vetis\controllers;

use api_web\classes\UserWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use api_web\modules\integration\modules\vetis\models\VetisWaybill;
use yii\filters\AccessControl;

/**
 * Class DefaultController
 *
 * @package api_web\modules\integration\modules\vetis\controllers
 */
class DefaultController extends WebApiController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'groups-list',
                        'short-info-vsd',
                        'full-info-vsd',
                        'filter-sender',
                        'filter-product',
                        'filter-status',
                        'filter-vsd',
                        'filters',
                        'get-vsd-pdf',
                        'list',
                        'return-vsd',
                        'partial-acceptance',
                        'repay-vsd',
                        'acquirer-filter',
                        'get-not-confirmed-vsd',
                        'regionalization-get',
                        'product-item-list',
                        'product-type-list',
                        'product-subtype-list',
                        'product-form-list',
                        'unit-list',
                        'packing-type-list',
                        'russian-enterprise-list',
                        'business-entity',
                        'ingredient-list',
                        'product-ingredient-list',
                        'product-info',
                        'create-product-item',
                        'create-transport',
                        'delete-transport',
                        'delete-ingredient',
                        'transport-storage-type-list',
                        'production-journal-list',
                        'production-journal-producer-filter',
                        'production-journal-sort',
                        'production-journal-short-info',
                    ],
                    'roles'      => [
                        Registry::MANAGER_RESTAURANT,
                        Registry::BOOKER_RESTAURANT,
                    ],
                    'roleParams' => ['user' => $this->user]
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\HttpException
     */
    public function beforeAction($action)
    {
        $this->setLicenseServiceId(Registry::MERC_SERVICE_ID);
        return parent::beforeAction($action);
    }

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
     *                                      "sender_name": "Поставщик №1(600021, обл.Владимирская, г.Муром,
     *                                      ул.Октябрьской Революции,д.2Б)",
     *                                      "product_name": "мясо верблюда",
     *                                      "status": "CONFIRMED",
     *                                      "status_date": "2018-08-30T13:11:02+03:00",
     *                                      "amount": "55.000",
     *                                      "unit": "кг",
     *                                      "production_date": "2018-07-02T03:00:00+03:00",
     *                                      "date_doc": "2018-08-30T15:00:00+03:00",
     *                                      "document_id": null,
     *                                      "status_text": "Статус",
     *                                      "r13nСlause": 1,
     *                                      "location_prosperity":"Неблагополучна"
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
     *                                "sender_name": "Поставщик №1 (600021, Владимерская обл., г. Муром, ул.
     *                                Октяборьской революции 16",
     *                                "status": "CONFIRMED",
     *                                "status_text": "Оформлен",
     *                                "status_date": "29.08.2018",
     *                                "amount": 40,
     *                                "unit": "кг",
     *                                "production_date": "29.08.2018",
     *                                "date_doc": "29.08.2018",
     *                                "r13nClause": 1,
     *                                "location_prosperity":"Неблагополучна"
     *                                },
     *                                {
     *                                "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
     *                                "product_name": "Говядина бескостная рубленая БИО, Замороженная",
     *                                "sender_name": "Поставщик №1 (600021, Владимерская обл., г. Муром, ул.
     *                                Октяборьской революции 16",
     *                                "status": "CONFIRMED",
     *                                "status_text": "Оформлен",
     *                                "status_date": "29.08.2018",
     *                                "amount": 40,
     *                                "unit": "кг",
     *                                "production_date": "29.08.2018",
     *                                "date_doc": "29.08.2018",
     *                                "r13nClause":0,
     *                                "location_prosperity":"Благополучна"
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
     * @throws \Exception
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
     *                  "search": {"sender_name":"часть имени", "acquirer_id": {7, 8}}
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
     *                          "a2667f4a-f91b-4752-b400-1bb129617de6": "Поставщик №1(600021, обл.Владимирская,
     *                          г.Муром, ул.Октябрьской Революции,д.2Б)"
     *                      }
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
     *                  "search": {"product_name":"часть имени", "acquirer_id": {7, 8}}
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
     * @throws \Exception
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
     *                             "a2667f4a-f91b-4752-b400-1bb129617de6": "Поставщик №1(600021, обл.Владимирская,
     *                             г.Муром, ул.Октябрьской Революции,д.2Б)",
     *                            "f8805c8f-1da4-4bda-aaca-a08b5d1cab1b": "Поставщик №2(600021, обл.Владимирская,
     *                            г.Муром, ул.Октябрьской Революции,д.2Б)"
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
     *                        "producer_name":"ООО Мираторг, 600021, Владимирская обл., г. Муром, ул. Октябрьской
     *                        революции 16",
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
     *                      "expertiseInfo": "ЭмпаерЛАБ эксп №1224 от 2018-05-17 ( Наличие паразитов - Паразиты
     *                      отстутствуют )",
     *                      "transport_type": "Автомобильный",
     *                      "transport_number": "54258",
     *                      "transport_storage_type": "Охлаждаемый",
     *                      "specified_person": "Понитков Максим Алексеевич",
     *                      "specified_person_post": "Project Manager"
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
     *                      "description":"long string description",
     *                      "conditions": {
     *                          "ed9839ef-5563-41a8-8f0a-c062ce0bca60",
     *                          "ed9839ef-5577-41a8-8f0a-c062ce0bca69"
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
     *                      "description":"long string description",
     *                      "conditions": {
     *                          "ed9839ef-5563-41a8-8f0a-c062ce0bca60",
     *                          "ed9839ef-5577-41a8-8f0a-c062ce0bca69"
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
     *                     "search": {"name":"часть имени"}
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
     */
    public function actionAcquirerFilter()
    {
        $this->response = (new UserWebApi())->getUserOrganizationBusinessList(null, $this->request['search']['name'] ?? null);
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
     *                      "org_id": {"1", "2", "3"}
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
     * @throws \Exception
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

    /**
     * @SWG\Post(path="/integration/vetis/regionalization-get",
     *     tags={"Integration/vetis"},
     *     summary="Получение информации по болезням",
     *     description="Получение информации по болезням",
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
     *                      "uuid": "ede52e76-6091-46bb-9349-87324ee1ae41",
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
     *                      "relocation": false,
     *                      "reason_for_prohibition": null,
     *                      "conditions": {
     *                      {
     *                      "name": "Сибирская язва",
     *                      "groups": {
     *                      {
     *                      {
     *                      "guid": "ed9839ef-5563-41a8-8f0a-c062ce0bca60",
     *                      "title": "Продукты убоя были получены от животных, которые были подвергнуты предубойному
     *                      осмотру и по его результатам не имели признаков сибирской язвы, продукты убоя были
     *                      подвернуты ветеринарно-санитарной экспертизе, по результатам которой не было выявлено
     *                      изменений, характерных для сибирской язвы",
     *                      "checked": false
     *                      }
     *                      }
     *                      }
     *                      }
     *                      }
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
    public function actionRegionalizationGet()
    {
        $this->response = (new VetisWaybill())->getRegionalizationInfo($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-item-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Наименования продукции",
     *     description="Получение списка Наименования продукции",
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
     *                     "business_id": 1,
     *                     "pagination": {
     *                         "page": 1,
     *                         "page_size": 12
     *                     }
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
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12
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
    public function actionProductItemList()
    {
        $this->response = (new VetisWaybill())->getProductItemList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-type-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Тип продукции",
     *     description="Получение списка Тип продукции",
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
     *                  {
     *                      "id": 1,
     *                      "name": "Мясо и мясопродукты."
     *                  },
     *                  {
     *                      "id": 2,
     *                      "name": "Корма и кормовые добавки."
     *                  },
     *                  {
     *                      "id": 3,
     *                      "name": "Живые животные."
     *                  },
     *                  {
     *                      "id": 4,
     *                      "name": "Лекарственные средства."
     *                  },
     *                  {
     *                      "id": 5,
     *                      "name": "Пищевые продукты."
     *                  },
     *                  {
     *                      "id": 6,
     *                      "name": "Непищевые продукты и другое."
     *                  },
     *                  {
     *                      "id": 7,
     *                      "name": "Рыба и морепродукты."
     *                  },
     *                  {
     *                      "id": 8,
     *                      "name": "Продукция, не требующая разрешения."
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
    public function actionProductTypeList()
    {
        $arResponse = [];
        foreach (VetisHelper::$vetis_product_types as $key => $item) {
            $arResponse[] = ['id' => $key, 'name' => $item];
        }
        $this->response = $arResponse;
    }

    /**
     * @SWG\Post(path="/integration/vetis/transport-storage-type-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка транспортных типов перевозки",
     *     description="Получение списка транспортных типов перевозки",
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
     *                  {
     *                      "id": 1,
     *                      "name": "замороженные"
     *                  },
     *                  {
     *                      "id": 2,
     *                      "name": "охлаженные"
     *                  },
     *                  {
     *                      "id": 3,
     *                      "name": "охлаждаемые"
     *                  },
     *                  {
     *                      "id": 4,
     *                      "name": "вентилируемые"
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
    public function actionTransportStorageTypeList()
    {
        $arResponse = [];
        foreach (VetisHelper::$transport_storage_types as $key => $item) {
            $arResponse[] = ['id' => $key, 'name' => $item];
        }
        $this->response = $arResponse;
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-subtype-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Продукция",
     *     description="Получение списка Продукция",
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
     *                     "type_id": 3
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
     *                      "name": "свиньи",
     *                      "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                  },
     *                  {
     *                      "name": "пчелы",
     *                      "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
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
    public function actionProductSubtypeList()
    {
        $this->response = (new VetisWaybill())->getProductSubtypeList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-form-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Вид Продукции",
     *     description="Получение списка Вид Продукции",
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
     *                     "guid": "41fb53ea-31c3-b116-9ce2-7d7df18c5835",
     *                     "search": {
     *                          "name": "хе"
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
     *                  {
     *                      "name": "хек тихоокеанский мороженый",
     *                      "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                      "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                  },
     *                  {
     *                      "name": "саварин мороженый",
     *                      "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                      "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
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
    public function actionProductFormList()
    {
        $this->response = (new VetisWaybill())->getProductFormList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/unit-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка единиц измерений из справочника меркурия",
     *     description="Получение списка единиц измерений из справочника меркурия",
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
     *                  {
     *                      "name": "тонна",
     *                      "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                      "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                  },
     *                  {
     *                      "name": "кг",
     *                      "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                      "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
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
    public function actionUnitList()
    {
        $this->response = (new VetisWaybill())->getUnitList();
    }

    /**
     * @SWG\Post(path="/integration/vetis/packing-type-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Тип упаковки",
     *     description="Получение списка Тип упаковки",
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
     *                  {
     *                      "name": "Бочка (емкостью около 164 л) деревянная шпунтованная",
     *                      "uuid": "021bc2d9-f514-4491-b21a-ffe63023236f",
     *                      "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                  },
     *                  {
     *                      "name": "Сундук, морской",
     *                      "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                      "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
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
    public function actionPackingTypeList()
    {
        $this->response = (new VetisWaybill())->getPackingTypeList();
    }

    /**
     * @SWG\Post(path="/integration/vetis/russian-enterprise-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Предприятий производителей",
     *     description="Получение списка Предприятий производителей",
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
     *                          "name": "Поставщик №2",
     *                          "uuid": "021bc2d9-f514-4491-b21a-ffe63023236f",
     *                          "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                      },
     *                      {
     *                          "name": "Поставщик №3",
     *                          "uuid": "004afcc5-6f7e-a246-425a-80c85095ec5b",
     *                          "guid": "99ebd7ac-fb42-44e1-a711-f82b365fc75a",
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
    public function actionRussianEnterpriseList()
    {
        $this->response = (new VetisWaybill())->getRussianEnterpriseList();
    }

    /**
     * @SWG\Post(path="/integration/vetis/business-entity",
     *     tags={"Integration/vetis"},
     *     summary="Получение Фирмы производителя",
     *     description="Получение Фирмы производителя",
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
     *                  {
     *                      "name": "ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ ОНЛАЙН МАРКЕТ",
     *                      "uuid": "021bc2d9-f514-4491-b21a-ffe63023236f",
     *                      "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
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
    public function actionBusinessEntity()
    {
        $this->response = (new VetisWaybill())->getBusinessEntity();
    }

    /**
     * @SWG\Post(path="/integration/vetis/ingredient-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Ингредиентов",
     *     description="Получение списка Ингредиентов",
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
     *                      "guid": "021bc2d9-f514-4491-b21a-ffe63023236f",
     *                      "search": {
     *                          "name": "ко"
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
     *                  "говядина КРУТЕЙШАЯ",
     *                  "лев белый"
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
    public function actionIngredientList()
    {
        $this->response = (new VetisWaybill())->getIngredientList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-ingredient-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Ингредиентов",
     *     description="Получение списка Ингредиентов",
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
     *                      "guid": "f73bad6a-8894-44e6-911e-b7f1c1e71466"
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
     *                      "product_name": "Ваниль",
     *                      "amount": "1.000",
     *                      "id": 2
     *                  },
     *                  {
     *                      "product_name": "Валерьянка",
     *                      "amount": "1.000",
     *                      "id": 3
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
    public function actionProductIngredientList()
    {
        $this->response = (new VetisWaybill())->getProductIngredientList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/product-info",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка Ингредиентов",
     *     description="Получение списка Ингредиентов",
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
     *                      "guid": "f73bad6a-8894-44e6-911e-b7f1c1e71466"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "name": "Агуша БиоКефир 3.2% 204г БЗ 12Х",
     *                  "uuid": "0001b743-21c1-41a3-aac0-bd4e6d21cfa6",
     *                  "guid": "308a4f21-a5fd-47c4-bb98-71a967999561",
     *                  "form": "кисломолочный напиток",
     *                  "article": "340033884",
     *                  "gtin": "4602541000035",
     *                  "gost": "ТУ 10.86.10-115-05268977-2014",
     *                  "active": 1,
     *                  "package_type": "кг",
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
    public function actionProductInfo()
    {
        $this->response = (new VetisWaybill())->getProductInfo($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/create-product-item",
     *     tags={"Integration/vetis"},
     *     summary="Добавление новой продукции в справочник наименований продукции",
     *     description="Добавление новой продукции в справочник наименований продукции",
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
     *                      "product_type": 3,
     *                      "form_guid": "20dda083-72d0-93e0-de2c-76ac4f88c5ce",
     *                      "subtype_guid": "98748766-2894-b5db-ab2f-035db5f44945",
     *                      "name": "new Kotleta()",
     *                      "article": "7d7df18c5832",
     *                      "gtin": "4602471014317",
     *                      "has_gost": true,
     *                      "gost": "ГОСТ-123",
     *                      "ingredients": {
     *                          {
     *                              "name": "Грибы",
     *                              "amount": 0.001,
     *                          },
     *                          {
     *                              "name": "Грибы",
     *                              "amount": 0.001,
     *                          }
     *                      },
     *                      "subject": "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                      "producers": {
     *                          "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                          "41fb53ea-31c3-b116-9ce2-7d7df18c5832"
     *                      },
     *                      "package": {
     *                          "type_guid": "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                          "amount": 1,
     *                          "volume": 12,
     *                          "unit_guid": "41fb53ea-31c3-b116-9ce2-7d7df18c5832"
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
     * @throws \Exception
     */
    public function actionCreateProductItem()
    {
        $this->response = (new VetisWaybill())->createProductItem($this->request, 'CREATE');
    }

    /**
     * @SWG\Post(path="/integration/vetis/update-product-item",
     *     tags={"Integration/vetis"},
     *     summary="Обновление продукции в справочник наименований продукции",
     *     description="Обновление продукции в справочник наименований продукции",
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
     *                      "uuid": "20dda083-72d0-93e0-de2c-76ac4f88c5ce",
     *                      "product_type": 3,
     *                      "form_guid": "20dda083-72d0-93e0-de2c-76ac4f88c5ce",
     *                      "subtype_guid": "98748766-2894-b5db-ab2f-035db5f44945",
     *                      "name": "new Kotleta()",
     *                      "article": "7d7df18c5832",
     *                      "gtin": "4602471014317",
     *                      "has_gost": true,
     *                      "gost": "ГОСТ-123",
     *                      "ingredients": {
     *                          {
     *                              "name": "Грибы",
     *                              "amount": 0.001,
     *                          },
     *                          {
     *                              "name": "Грибы",
     *                              "amount": 0.001,
     *                          }
     *                      },
     *                      "subject": "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                      "producers": {
     *                          "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                          "41fb53ea-31c3-b116-9ce2-7d7df18c5832"
     *                      },
     *                      "package": {
     *                          "type_guid": "41fb53ea-31c3-b116-9ce2-7d7df18c5832",
     *                          "amount": 1,
     *                          "volume": 12,
     *                          "unit_guid": "41fb53ea-31c3-b116-9ce2-7d7df18c5832"
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
     * @throws \Exception
     */
    public function actionUpdateProductItem()
    {
        $this->response = (new VetisWaybill())->createProductItem($this->request, 'UPDATE');
    }

    /**
     * @SWG\Post(path="/integration/vetis/create-transport",
     *     tags={"Integration/vetis"},
     *     summary="Добавление нового транспорта в справочник Транспортные средства",
     *     description="Если не отправлять org_id создат ТС в текущей организации",
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
     *                      "vehicle_number": "1",
     *                      "trailer_number": "1",
     *                      "container_number": "1",
     *                      "transport_storage_type": 1,
     *                      "org_id": 1
     *                  }
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
     * @throws \Exception
     */
    public function actionCreateTransport()
    {
        $this->response = (new VetisWaybill())->createTransport($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/delete-transport",
     *     tags={"Integration/vetis"},
     *     summary="Удаление транспорта из справочника Транспортные средства",
     *     description="Если не отправлять org_id попробует удалить ТС в текущей организации",
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
     *                      "id": 1,
     *                      "org_id": 1
     *                  }
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
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeleteTransport()
    {
        $this->response = (new VetisWaybill())->deleteTransport($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/delete-ingredient",
     *     tags={"Integration/vetis"},
     *     summary="Удаление ингредиента продукции из нашего внутреннего справочника",
     *     description="Удаление ингредиента продукции из нашего внутреннего справочника",
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
     *                      "id": 1,
     *                      "org_id": 1
     *                  }
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
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeleteIngredient()
    {
        $this->response = (new VetisWaybill())->deleteIngredient($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/production-journal-list",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка продукции в Журнал продукции",
     *     description="Получение списка продукции в Журнал продукции",
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
     *                          "production_name": "Кролик фермерский",
     *                          "name": "Кро",
     *                          "producer_guid": "f8805c8f-1da4-4bda-aaca-a08b5d1cab1b",
     *                          "create_date": {
     *                              "from": "23.08.2018",
     *                              "to": "24.08.2018"
     *                          },
     *                          "production_date": {
     *                              "from": "23.08.2018",
     *                              "to": "24.08.2018"
     *                          },
     *                          "expiry_date": {
     *                              "from": "23.08.2018",
     *                              "to": "24.08.2018"
     *                          }
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": {
     *                          "product_name",
     *                          "create_date",
     *                          "expiry_date"
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
     *                      {
     *                          "number": "13786453",
     *                          "name": "курица домашняя: cубпродукты охлажденные",
     *                          "uuid": "00f4334f-23d5-468b-81c7-258f097bab0e",
     *                          "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                          "producer": "ЕвроТрейдБрест ООО, СП(г. Брест, ул. Катин Бор, 111б)",
     *                          "country": "Беларусь",
     *                          "balance": "35.000",
     *                          "unit": "кг",
     *                          "created_at": "2018-07-30T09:39:07+03:00",
     *                          "production_date": "2018-05-07T03:00:00+03:00",
     *                          "expiry_date": "2018-5-15 0:00:00"
     *                      },
     *                      {
     *                          "number": "13786453",
     *                          "name": "курица домашняя: cубпродукты охлажденные",
     *                          "uuid": "00f4334f-23d5-468b-81c7-258f097bab0e",
     *                          "guid": "0eff77d2-bb8a-470c-8124-2bcc0a7f814c",
     *                          "producer": "ЕвроТрейдБрест ООО, СП(г. Брест, ул. Катин Бор, 111б)",
     *                          "country": "Беларусь",
     *                          "balance": "35.000",
     *                          "unit": "кг",
     *                          "created_at": "2018-07-30T09:39:07+03:00",
     *                          "production_date": "2018-05-07T03:00:00+03:00",
     *                          "expiry_date": "2018-5-15 0:00:00"
     *                      }
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12
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
     * @throws \Throwable
     */
    public function actionProductionJournalList()
    {
        $this->response = (new VetisWaybill())->getStockEntryList($this->request);
    }

    /**
     * @SWG\Post(path="/integration/vetis/production-journal-producer-filter",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка фильтра Производители",
     *     description="Получение списка фильтра Производители",
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
     *                  {
     *                      "producer_name": "Вулканный, ООО Далькреветка(ул.Дзер жинского,36 г. Южно-Сахалинск)",
     *                      "producer_guid": "694f631a-72d6-4208-994e-14b62ad418e2"
     *                  },
     *                  {
     *                      "producer_name": "ЗАО Микояновский МК(г.Москва, ул.Пермская, вл.3)",
     *                      "producer_guid": "cb5804ed-b479-c9ad-7479-df50d6db71f2"
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
     * @throws \Throwable
     */
    public function actionProductionJournalProducerFilter()
    {
        $this->response = (new VetisWaybill())->getProductionJournalProducerFilter();
    }

    /**
     * @SWG\Post(path="/integration/vetis/production-journal-sort",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка сортировки",
     *     description="Получение списка сортировки",
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
     *                  "product_name": "Названию продукции А-Я",
     *                  "-product_name": "Названию продукции А-Я",
     *                  "create_date": "Дате создания продукции по возрастанию",
     *                  "-create_date": "Дате создания продукции по убыванию",
     *                  "expiry_date": "Сроку годности продукции по возрастанию",
     *                  "-expiry_date": "Сроку годности продукции по убыванию"
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
     * @throws \Throwable
     */
    public function actionProductionJournalSort()
    {
        $this->response = (new VetisWaybill())->getProductionJournalSort();
    }

    /**
     * @SWG\Post(path="/integration/vetis/production-journal-short-info",
     *     tags={"Integration/vetis"},
     *     summary="Получение списка сортировки",
     *     description="Получение списка сортировки",
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
     *                      "uuid": "94a54162-a7ca-4534-9f96-39cacc934e8f"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "product_form": "говядина",
     *                  "batch_id": "123456",
     *                  "packing": null
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
     * @throws \Throwable
     */
    public function actionProductionJournalShortInfo()
    {
        $this->response = (new VetisWaybill())->getProductionJournalShortInfo($this->request);
    }

}
