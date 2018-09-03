<?php

/**
 * Class SystemController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-03
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class SystemController
 * @package api_web\controllers
 */
class SystemController extends WebApiController
{

    /**
     * @SWG\Post(path="/system/datetime",
     *     tags={"System"},
     *     summary="Посмотреть параметры работы сервера со временем ",
     *     description="Посмотреть различные способы вывода localtime с помощью популярных функций PHP",
     *     produces={"application/html", "application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={
     *                 "localtime()": "var_dump() --- string",
     *                 "getdate()": "var_dump() --- string",
     *                 "microtime(1)": "var_dump() --- string",
     *                 "time": "var_dump() --- string",
     *                 "gmdate('Y-m-d H:i:s')": "var_dump() --- string",
     *                 "date('Y-m-d H:i:s')": "var_dump() --- string",
     *                 "date_default_timezone_get()": "var_dump() --- string"
     *             }
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
    public function actionDatetime()
    {
        $this->response = $this->container->get('SystemWebApi')->datetime();
    }

}