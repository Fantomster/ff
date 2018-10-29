<?php

namespace api_web\controllers;

/**
 * Class DocumentController
 *
 * @package api_web\controllers
 */
class DocumentController extends \api_web\components\WebApiController
{
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
        $this->response = $this->container->get('DocumentWebApi')->getDocumentContents($this->request);
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
     *                         "doc_number" : "12346",
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
     *                      {
     *                        "id": 11326,
     *                        "doc_number": "тест",
     *                        "type": "order",
     *                        "status_id": 6,
     *                        "status_text": "Отменен клиентом",
     *                        "service_id": 9,
     *                        "is_mercury_cert": false,
     *                        "count": 10,
     *                        "total_price": "500.00",
     *                        "doc_date": "2018-04-11T18:41:34+03:00",
     *                        "vendor": {
     *                          "id": 4749,
     *                          "name": "Demo1",
     *                          "difer": false
     *                        },
     *                        "agent": null,
     *                        "store": null
     *                      },
     *                      {
     *                         "id": 11327,
     *                         "doc_number": null,
     *                         "type": "order",
     *                         "status_id": 4,
     *                         "status_text": "Завершен",
     *                         "service_id": 3,
     *                         "is_mercury_cert": false,
     *                         "count": 10,
     *                         "total_price": "1173.20",
     *                         "doc_date": "2018-04-11T18:41:42+03:00",
     *                         "vendor": {
     *                         "id": 5158,
     *                         "name": "MixCart Поставщик",
     *                         "difer": false
     *                          },
     *                         "agent": null,
     *                         "store": null
     *                      },
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
        $this->response = $this->container->get('DocumentWebApi')->getDocumentsList($this->request);

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
     */
    public function actionWaybillDetail()
    {
        $this->response = $this->container->get('DocumentWebApi')->getWaybillDetail($this->request);
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
     */
    public function actionUpdateWaybillDetail()
    {
        $this->response = $this->container->get('DocumentWebApi')->editWaybillDetail($this->request);
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
     */
    public function actionResetWaybillPositions()
    {
        $this->response = $this->container->get('DocumentWebApi')->waybillResetPositions($this->request);
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
     */
    public function actionMapWaybillOrder()
    {
        $this->response = $this->container->get('DocumentWebApi')->mapWaybillOrder($this->request);
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
        $this->response = $this->container->get('DocumentWebApi')->getDocumentStatus();
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
        $this->response = $this->container->get('DocumentWebApi')->getWaybillStatus();
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
     *                       "doc_number": "Номеру документа А-Я",
     *                       "-doc_number": "Номеру документа Я-А",
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
        $this->response = $this->container->get('DocumentWebApi')->getSortList();
    }
}