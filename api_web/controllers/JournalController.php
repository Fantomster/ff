<?php
namespace api_web\controllers;

use api_web\classes\JournalWebApi;
use api_web\components\WebApiController;

/**
 * Class JournalController
 *
 * @package api_web\controllers
 */
class JournalController extends WebApiController
{
    /**
     * @SWG\Post(path="/journal/list",
     *     tags={"Journal"},
     *     summary="Список записей журнала",
     *     description="Список записей журнала",
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
     *                      "search": {
     *                         "date": {
     *                             "start": "23.08.2018",
     *                             "end": "24.08.2018"
     *                         },
     *                         "user_id": 3768,
     *                         "service_id": 2,
     *                         "type": "error"
     *                      },
     *                      "pagination": {
     *                          "page": 1,
     *                          "page_size": 12
     *                      },
     *                      "sort": "response, created_at"
     *                  }
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
     * @throws \Exception
     */
    public function actionList()
    {
        $this->response = (new JournalWebApi())->list($this->request);
    }
}