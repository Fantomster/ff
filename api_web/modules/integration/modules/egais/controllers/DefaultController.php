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
        $this->response = (new EgaisMethods())->setEgaisSettings($this->request, $this->user->organization_id);
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
     *
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
     *
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
     * @SWG\Post(path="/integration/egais/query-rests",
     *     tags={"Integration/egais"},
     *     summary="ЕГАИС запрос остатков",
     *     description="запрашиваем остатки алкогольной продукции в ЕГАИС",
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
    public function actionQueryRests()
    {
        $this->response = (new EgaisMethods())->getQueryRests($this->request);
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
}