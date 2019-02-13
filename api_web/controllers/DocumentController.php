<?php

namespace api_web\controllers;

use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use yii\filters\AccessControl;

/**
 * Class DocumentController
 *
 * @property DocumentWebApi $classWebApi
 * @package api_web\controllers
 */
class DocumentController extends \api_web\components\WebApiController
{
    public $className = DocumentWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'documents-list',
                        'document-content',
                        'waybill-detail',
                        'update-waybill-detail',
                        'reset-waybill-positions',
                        'map-waybill-order',
                        'document-status',
                        'waybill-status',
                        'get',
                        'sort-list',
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
        $this->setLicenseServiceId($this->request['service_id'] ?? null);
        return parent::beforeAction($action);
    }

    /**
     * @SWG\Post(path="/document/document-content",
     *     tags={"Documents"},
     *     summary="Детальная часть документа",
     *     description="Детальная часть документа
     *     Типы возвращаемых данных:
     *     https://goo.gl/VSWoBC
     *
     *     has_order_content - если не задан или null вернет все
     *                       - false вернет только без привязки к заказу
     *                       - true вернет только с привязкой к заказу
     * ",
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
     *                      "document_id": 2,
     *                      "type": "order",
     *                      "service_id": 2,
     *                      "has_order_content": true
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws \Exception
     */
    public function actionDocumentContent()
    {
        $this->response = $this->classWebApi->getDocumentContents($this->request);
    }

    /**
     * @SWG\Post(path="/document/documents-list",
     *     tags={"Documents"},
     *     summary="Список документов",
     *     description="Список документов",
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
     *                         "business_id": 124,
     *                         "waybill_status": 1,
     *                         "number" : "12346",
     *                         "waybill_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         },
     *                         "order_date": {
     *                             "from": "23.08.2018",
     *                             "to": "24.08.2018"
     *                         },
     *                        "vendor" : {
     *                          1254,
     *                          3256
     *                       },
     *                      "store" : {
     *                          1254,
     *                          3256
     *                       },
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "id"
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *          @SWG\Schema(
     *              default={
     *                  "documents": {
     *                     {
     *                           "id": 11326,
     *                           "number": {
     *                           "1",
     *                           "2",
     *                           "3",
     *                           "4",
     *                           "тест",
     *                           "6",
     *                           "7",
     *                           "8",
     *                           "9",
     *                           "10"
     *                           },
     *                           "type": "order",
     *                           "status_id": 2,
     *                           "status_text": "Ожидает формирования",
     *                           "service_id": 9,
     *                           "is_mercury_cert": 1,
     *                           "count": 10,
     *                           "total_price": "3390.00",
     *                           "doc_date": "2018-04-11T18:41:34+03:00",
     *                           "vendor": {
     *                              "id": "4749",
     *                              "name": "Demo1",
     *                              "difer": false
     *                           },
     *                           "agent": {
     *                              "id": 1111,
     *                              "name": "Test",
     *                              "count": 1
     *                           },
     *                           "store":  {
     *                              "id": 2222,
     *                              "name": "Test"
     *                           },
     *                           "replaced_order_id": null
     *                           },
     *                           {
     *                           "id": 13508,
     *                             "number": {
     *                               "888"
     *                               },
     *                           "type": "order",
     *                           "status_id": 2,
     *                           "status_text": "Сформирована",
     *                           "service_id": 2,
     *                           "is_mercury_cert": 0,
     *                           "count": 2,
     *                           "total_price": "10700.00",
     *                           "doc_date": "2018-10-18T14:48:36+03:00",
     *                           "vendor": {
     *                           "id": "5440",
     *                           "name": "ООО Организация поставок",
     *                           "difer": false
     *                           },
     *                           "agent": null,
     *                           "store": null,
     *                           "replaced_order_id": null
     *                           },
     *                  },
     *                  "pagination": {
     *                      "page": 1,
     *                      "page_size": 12,
     *                      "total_page": 17
     *                  },
     *                  "sort": "id"
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws \Exception
     */
    public function actionDocumentsList()
    {
        $this->response = $this->classWebApi->getDocumentsList($this->request);
    }

    /**
     * @SWG\Post(path="/document/waybill-detail",
     *     tags={"Documents"},
     *     summary="Накладная - Детальная информация ",
     *     description="Накладная - Детальная информация ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"waybill_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                              "id": 226667,
     *                              "code": 226667,
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               },
     *                               "store": {
     *                               "uid": "3489",
     *                               "name": "Горячий цех",
     *                               },
     *                               "doc_date": "2018-09-04T09:55:22+03:00",
     *                               "outer_number_additional": "22666-111-1",
     *                               "outer_number_code": 22666,
     *                               "payment_delay_date": "2018-09-17T09:55:22+03:00",
     *                               "outer_note": "Примечание"
     *                  }
     *         ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws
     */
    public function actionWaybillDetail()
    {
        $this->response = $this->classWebApi->getWaybillDetail($this->request);
    }

    /**
     * @SWG\Post(path="/document/update-waybill-detail",
     *     tags={"Documents"},
     *     summary="Накладная - Обновление детальной информации",
     *     description="Накладная - Обновление детальной информации",
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
     *                      "id": 226667,
     *                      "agent_uid": "11232123",
     *                      "store_uid": "3489",
     *                      "doc_date": "2018-09-04T09:55:22+03:00",
     *                      "outer_number_additional": "22666-111-1",
     *                      "outer_number_code": 22666,
     *                      "payment_delay_date": "2018-09-17T09:55:22+03:00",
     *                      "outer_note": "Примечание"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                              "id": 226667,
     *                              "code": 226667,
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               },
     *                               "store": {
     *                               "uid": "3489",
     *                               "name": "Горячий цех",
     *                               },
     *                               "doc_date": "2018-09-04T09:55:22+03:00",
     *                               "outer_number_additional": "22666-111-1",
     *                               "outer_number_code": 22666,
     *                               "payment_delay_date": "2018-09-17T09:55:22+03:00",
     *                               "outer_note": "Примечание"
     *                  }
     *         ),
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
     * @throws
     */
    public function actionUpdateWaybillDetail()
    {
        $this->response = $this->classWebApi->editWaybillDetail($this->request);
    }

    /**
     * @SWG\Post(path="/document/reset-waybill-positions",
     *     tags={"Documents"},
     *     summary="Накладная - Сброс позиций ",
     *     description="Накладная - Сброс позиций ",
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
     *                      "waybill_id": 1111
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *          @SWG\Schema(
     *              default={
     *                  "result": true
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws
     */
    public function actionResetWaybillPositions()
    {
        $this->response = $this->classWebApi->waybillResetPositions($this->request);
    }

    /**
     * @SWG\Post(path="/document/map-waybill-order",
     *     tags={"Documents"},
     *     summary="Накладная - Сопоставление с заказом ",
     *     description="Накладная - Сопоставление с заказом ",
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
     *                      "document_id": 1111,
     *                      "replaced_order_id": 2525
     *                      }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *          @SWG\Schema(
     *              default={
     *                  "result": true
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws
     */
    public function actionMapWaybillOrder()
    {
        $this->response = $this->classWebApi->mapWaybillOrder($this->request);
    }

    /**
     * @SWG\Post(path="/document/document-status",
     *     tags={"Documents"},
     *     summary="Статусы документов (групповые)",
     *     description="Статусы документов (групповые)",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "status": {
     *                      "1": "Ожидают выгрузки",
     *                      "2": "Ожидают формирования",
     *                      "3": "Выгружена"
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     */
    public function actionDocumentStatus()
    {
        $this->response = $this->classWebApi->getDocumentStatus();
    }

    /**
     * @SWG\Post(path="/document/waybill-status",
     *     tags={"Documents"},
     *     summary="Статусы накладных",
     *     description="Статусы накладных",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "status": {
     *                      "1": "Сопоставлена",
     *                      "2": "Сформирована",
     *                      "3": "Ошибка",
     *                      "4": "Сброшена",
     *                      "5": "Выгружена"
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws \Exception
     */
    public function actionWaybillStatus()
    {
        $this->response = $this->classWebApi->getWaybillStatus();
    }

    /**
     * @SWG\Post(path="/document/get",
     *     tags={"Documents"},
     *     summary="Получение документа",
     *     description="Получение документа",
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
     *                      "document_id": 7,
     *                      "type": "waybill",
     *                      "service_id": 2
     *                 }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  {
     *                      "document": {
     *                          "id": 83,
     *                          "number": {
     *                              "13392-1"
     *                          },
     *                          "type": "waybill",
     *                          "status_id": 1,
     *                          "status_text": "Сопоставлена",
     *                          "service_id": 2,
     *                          "vendor": {
     *                              "id": 5785,
     *                              "name": "ООО AAAAA"
     *                          },
     *                          "agent": null,
     *                          "store": {
     *                              "id": 9,
     *                              "name": "Основной склад"
     *                          },
     *                          "is_mercury_cert": false,
     *                          "count": 1,
     *                          "total_price": "4998.00",
     *                          "doc_date": "2018-10-26T14:36:54+03:00"
     *                      },
     *                      "documents": {},
     *                      "positions": {
     *                          {
     *                              "id": 26,
     *                              "product_id": 1640035,
     *                              "product_name": "Треска горячего копчения",
     *                              "outer_product": {
     *                                  "id": 1565080,
     *                                  "name": "____сосиска2"
     *                              },
     *                              "quantity": 1,
     *                              "outer_unit": {
     *                                  "id": 14,
     *                                  "name": "кг"
     *                              },
     *                              "koef": 1,
     *                              "merc_uuid": null,
     *                              "sum_without_vat": "4998.00",
     *                              "sum_with_vat": "4998.00",
     *                              "vat": 0
     *                          }
     *                      }
     *                  }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws \Exception
     */
    public function actionGet()
    {
        $this->response = $this->classWebApi->getDocument($this->request);
    }

    /**
     * @SWG\Post(path="/document/sort-list",
     *     tags={"Documents"},
     *     summary="Список сортировок",
     *     description="Список сортировок",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                   {
     *                       "number": "Номеру документа А-Я",
     *                       "-number": "Номеру документа Я-А",
     *                       "doc_date": "Дате документа по возрастанию",
     *                       "-doc_date": "Дате документа по убванию"
     *                   }
     *              }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     * )
     * @throws \Exception
     */
    public function actionSortList()
    {
        $this->response = $this->classWebApi->getSortList();
    }
}
