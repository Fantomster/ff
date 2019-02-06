<?php

namespace api_web\modules\integration\modules\rkeeper\controllers;

use api_web\classes\RkeeperWebApi;
use api_web\components\WebApiController;

class StoreController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/rkeeper/store/list",
     *     tags={"Integration/rkeeper/store"},
     *     summary="Справочник складов",
     *     description="Справочник складов, view_type - 1 плоский вид, 0 - дерево",
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
     *                                  "view_type":1
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       {
     *                          "id": 33251,
     *                          "rid": "10",
     *                          "name":"Склад 1",
     *                          "type": 0,
     *                          "level": 0,
     *                          "items" : {
     *                              {
     *                              "id": 33252,
     *                              "rid": 11,
     *                              "name":"Длительного хранения",
     *                              "type": 2,
     *                              "level": 1
     *                              }
     *                          }
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
     * @throws
     */
    public function actionList()
    {
        $this->response = (new RkeeperWebApi())->getStoreList($this->request);
    }
}
