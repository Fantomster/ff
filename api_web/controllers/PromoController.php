<?php

namespace api_web\controllers;

use api_web\classes\PromoWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

/**
 * Class PromoController
 *
 * @property PromoWebApi $classWebApi
 * @package api\modules\v1\modules\web\controllers
 */
class PromoController extends WebApiController
{
    public $className = PromoWebApi::class;

    /**
     * @SWG\Post(path="/promo/send",
     *     tags={"Promo"},
     *     summary="Отправка сообщения",
     *     description="Метод отправки сообщений",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                          "lead_name":"Ресторан",
     *                          "lead_email":"test@test.com",
     *                          "lead_city":"Город",
     *                          "action_id":33,
     *                          "promo_code":"3333"
     *                          }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *{
     *       "result": "true"
     *}
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     * @throws
     */
    public function actionSend()
    {
        $this->response = $this->classWebApi->send($this->request);
    }
}
