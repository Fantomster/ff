<?php

namespace api_web\controllers;

use api_web\classes\CartWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;

/**
 * Class DefaultController
 *
 * @package api_web\controllers
 */
class DefaultController extends WebApiController
{
    /**
     * @SWG\Post(path="/default/get-nds-list",
     *     tags={"Default"},
     *     summary="Список ставок НДС",
     *     description="Список ставок НДС",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                  0,10,18
     *             }
     *          ),
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
    public function actionGetNdsList()
    {
        $this->response = (new CartWebApi())->getVatsByOrganization($this->request);
    }
}