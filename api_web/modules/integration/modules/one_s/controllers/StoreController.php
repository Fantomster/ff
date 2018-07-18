<?php

namespace api_web\modules\integration\modules\one_s\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\one_s\models\one_sStore;

class StoreController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/one_s/store/list",
     *     tags={"Integration/one_s/store"},
     *     summary="Список складов",
     *     description="Список складов",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"search":{"name":"склад"}}
     *              )
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
     */
    public function actionList()
    {
        $this->response = (new one_sStore())->list($this->request);
    }
}