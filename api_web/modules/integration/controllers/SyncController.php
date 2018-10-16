<?php

/**
 * Class SyncController
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use api_web\modules\integration\classes\SyncLog;
use api_web\modules\integration\classes\SyncServiceFactory;
use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\components\WebApiController;

/**
 * Class SyncController
 * Routing R-keeper synchronization for different dictionaries
 *
 * @package api_web\modules\integration\controllers
 */
class SyncController extends WebApiController
{
    /**
     * @SWG\Post(path="/integration/sync/run",
     *     tags={"Integration/sync/run"},
     *     summary="Универсальный метод интеграционных действий",
     *     description="Универсальный метод интеграционных действий по синхронизации данных с внешней системой
     * Доступные значения:
     *     service_id: 1, //Сделано только для R-keeper
     *         params: {
     *             dictionary: {
     *                              agent,
     *                              category,
     *                              product,
     *                              store,
     *                              unit,
     *                           }
     *             product_group: 97 // Группа выбранная пользователем,
     *         }
     *
     *     ",
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
     *                      "service_id": 1,
     *                      "params": {
     *                          "dictionary": "agent",
     *                          "product_group": 97
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
     *                  "result": {
     *                      "task_id": 2763,
     *                      "task_status": 1
     *                  }
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
     *
     * Multifunctoinal integration method
     * @throws BadRequestHttpException
     */
    public function actionRun()
    {

        # 1. Get `task_id` if in query params
        $task_id = Yii::$app->getRequest()->getQueryParam(AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);

        # 2. Checkout other params and fix callback
        if ($task_id) {

            # 2.1.1.Trace callback operation with task_id
            SyncLog::trace('Callback operation `task_id` params is ' . $task_id);

            # 2.1.2. Check root script params
            if (!isset($this->request['service_id'])) {
                $this->request['service_id'] = null;
            }
            SyncLog::trace('Service ID is: ' . $this->request['service_id']);
            if (!isset($this->request['params'])) {
                $this->request['params'] = [];
            }
            SyncLog::trace('Request params are: ' . json_encode($this->request['params']));

        } else {

            # 2.2.1. Check root script params
            if (!isset($this->request['service_id']) || !$this->request['service_id'] || !is_int($this->request['service_id'])) {
                SyncLog::trace('"Service ID" is required and empty!');
                throw new BadRequestHttpException("empty_param|service_id");
            }
            if (!isset($this->request['params']) || !is_array($this->request['params']) || !$this->request['params']) {
                SyncLog::trace('Required variable "params" is empty!');
                throw new BadRequestHttpException("empty_param|params");
            }
            SyncLog::trace('Fix non-callback operation scenario');

        }

        # 3. Load integration script with env and post params
        $this->response = (new SyncServiceFactory($this->request['service_id'], $this->request['params'], $task_id))->syncResult;

    }

}