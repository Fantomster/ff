<?php

namespace api_web\controllers;

class DocumentController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/document/document-content",
     *     tags={"Documents/content"},
     *     summary="Детальная часть документа",
     *     description="Детальная часть документа",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/Document"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "document_id": 2,
     *                      "type": "order"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  "documents": {
     *                      {
     *                              "id": 22666,
     *                               "type": "order",
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               "difer": false
     *                               },
     *                               "vendor": {
     *                               "id": 3489,
     *                               "name": "Halal Organic Food",
     *                               "difer": false
     *                               },
     *                               "is_mercury_cert": true,
     *                               "count": 134,
     *                               "total_price": 3214222.95,
     *                               "doc_date": "2018-09-04T09:55:22+03:00"
     *                      },
     *                      {
     *                               "id": 22666,
     *                               "type": "order",
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               "difer": false
     *                               },
     *                               "vendor": {
     *                               "id": 3489,
     *                               "name": "Halal Organic Food",
     *                               "difer": false
     *                               },
     *                               "is_mercury_cert": true,
     *                               "count": 134,
     *                               "total_price": 3214222.95,
     *                               "doc_date": "2018-09-04T09:55:22+03:00"
     *                     }
     *                  },
     *                  "positions": {
     *                      {
     *                               "id" => 2222,
     *                               "product_id" => 3212,
     *                               "product_name" => "Апелисны",
     *                               "product_outer_id" => 456789,
     *                               "quantity" => "Апелисны импортные",
     *                               "unit" => "кг",
     *                               "koef" => 1,
     *                               "sum_without_vat" => 563789.05,
     *                               "sum_with_vat" => 542364.25,
     *                               "vat" => 18,
     *                               },
     *                       {
     *                               "id" => 2222,
     *                               "product_id" => 3212,
     *                               "product_name" => "Апелисны",
     *                               "product_outer_id" => 456789,
     *                               "quantity" => "Апелисны импортные",
     *                               "unit" => "кг",
     *                               "koef" => 1,
     *                               "sum_without_vat" => 563789.05,
     *                               "sum_with_vat" => 542364.25,
     *                               "vat" => 18,
     *                              },
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
    public function actionDocumentContent()
    {
        $this->response = $this->container->get('DocumentWebApi')->getDocumentContents($this->request);
    }

    /**
     * @SWG\Post(path="/document/documents-list",
     *     tags={"Documents/list"},
     *     summary="Список документов",
     *     description="Список документов",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/Document"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
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
     *                         }
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
     *                              "id": 22666,
     *                               "type": "order",
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               "difer": false
     *                               },
     *                               "vendor": {
     *                               "id": 3489,
     *                               "name": "Halal Organic Food",
     *                               "difer": false
     *                               },
     *                               "is_mercury_cert": true,
     *                               "count": 134,
     *                               "total_price": 3214222.95,
     *                               "doc_date": "2018-09-04T09:55:22+03:00"
     *                      },
     *                      {
     *                               "id": 22666,
     *                               "type": "order",
     *                               "status_id": 1,
     *                               "status_text": "Ожидают формирования",
     *                               "agent": {
     *                               "uid": "11232123",
     *                               "name": "Опт Холод",
     *                               "difer": false
     *                               },
     *                               "vendor": {
     *                               "id": 3489,
     *                               "name": "Halal Organic Food",
     *                               "difer": false
     *                               },
     *                               "is_mercury_cert": true,
     *                               "count": 134,
     *                               "total_price": 3214222.95,
     *                               "doc_date": "2018-09-04T09:55:22+03:00"
     *                     }
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
     */
    public function actionDocumentsList()
    {
        $this->response = $this->container->get('DocumentWebApi')->getDocumentsList($this->request);
    }
}