<?php

/**
 * Class SyncController
 *
 * @package   api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace api_web\modules\integration\controllers;

use Yii;
use yii\web\BadRequestHttpException;
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
     *     tags={"Integration/sync"},
     *     summary="Универсальный метод интеграционных действий",
     *     description="Универсальный метод интеграционных действий по синхронизации данных с внешней системой
     *    Доступные значения:
     *     service_id: 1,
     *     params: {
     *       dictionary: {
     *         agent,
     *         category,
     *         product,
     *         store,
     *         unit,
     *       }
     *     }",
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
     *                          "dictionary": "agent"
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default= {
     *                      "id": 2,
     *                      "name": "store",
     *                      "title": "Склады",
     *                      "count": 5,
     *                      "status_id": 3,
     *                      "status_text": "Запрос отправлен",
     *                      "created_at": "2018-10-18T17:01:57+03:00",
     *                      "updated_at": "2018-11-07T17:21:27+03:00"
     *            }
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
            if (!isset($this->request['service_id']) || !$this->request['service_id'] || !is_int($this->request['service_id'])) {
                throw new BadRequestHttpException("empty_param|service_id");
            }
            if (!isset($this->request['params']) || !is_array($this->request['params']) || !$this->request['params']) {
                SyncLog::trace('Required variable "params" is empty!');
                throw new BadRequestHttpException("empty_param|params");
            }
        }
        # 3. Load integration script with env and post params
        $this->response = (new SyncServiceFactory($this->request['service_id'], $this->request['params'], $task_id))->syncResult;
    }

    /**
     * @SWG\Post(path="/integration/sync/send-waybill",
     *     tags={"Integration/sync"},
     *     summary="Метод выгрузки накладных во внешнюю систему",
     *     description="Метод выгрузки накладных во внешнюю систему",
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
     *                      "ids": {
     *                          1,
     *                          2,
     *                          3
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
     *                       "1": true,
     *                       "2": true,
     *                       "3": false,
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
    public function actionSendWaybill()
    {
        # 2.2.1. Check root script params
        if (!isset($this->request['service_id']) || !$this->request['service_id'] || !is_int($this->request['service_id'])) {
            SyncLog::trace('"Service ID" is required and empty!');
            throw new BadRequestHttpException("empty_param|service_id");
        }
        if (!isset($this->request['ids']) || !is_array($this->request['ids']) || !$this->request['ids']) {
            SyncLog::trace('Required variable "ids" is empty!');
            throw new BadRequestHttpException("empty_param|ids");
        }
        SyncLog::trace('Fix non-callback operation scenario');

        # 3. Load integration script with env and post params
        $factory = (new SyncServiceFactory($this->request['service_id'], [], SyncServiceFactory::TASK_SYNC_GET_LOG))->factory($this->request['service_id']);

        $this->response = $factory->sendWaybill($this->request);
    }
}