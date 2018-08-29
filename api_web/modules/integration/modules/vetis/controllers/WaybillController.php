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
}