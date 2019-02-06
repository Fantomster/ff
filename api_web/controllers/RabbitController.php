<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:31 PM
 */

namespace api_web\controllers;

use api_web\classes\RabbitWebApi;
use api_web\components\WebApiController;

/**
 * Class RabbitController
 *
 * @property RabbitWebApi $classWebApi
 * @package api_web\controllers
 */
class RabbitController extends WebApiController
{
    public $className = RabbitWebApi::class;
    /**
     * @SWG\Post(path="/rabbit/add-to-queue",
     *     tags={"Rabbit"},
     *     summary="Добавить сообщение в очередь",
     *     description="Добавить сообщение в очередь",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"queue":"IikoProductSync", "org_id":5144}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"result":true}
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
     * @throws \Exception
     */
    public function actionAddToQueue()
    {
        $this->response = $this->classWebApi->addToQueue($this->request);
    }
}
