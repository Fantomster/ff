<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

class IntegrationController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/list",
     *     tags={"Интеграция"},
     *     summary="Список провайдеров интерграции",
     *     description="Список провайдеров интерграции",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationProviderList"),
     *     )
     * )
     */
    public function actionList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->list($this->request);
    }
}