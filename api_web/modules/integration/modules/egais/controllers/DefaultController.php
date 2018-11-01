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
     * @SWG\Post(path="/integration/egais/query-rests",
     *     tags={"Integration/egais"},
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
    public function actionQueryRests()
    {
        $this->response = (new EgaisMethods())->getQueryRests($this->request);
    }
}