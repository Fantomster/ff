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
     *                    "egaisUrl": "http://192.168.1.70:8090",
     *                    "fsrarId": "030000443640",
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
        $this->response = (new EgaisMethods())->setEgaisSettings($this->request);
    }
}