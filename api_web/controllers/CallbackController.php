<?php

/**
 * Class CallbackController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-04
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\controllers;

use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use common\models\OuterTask;
use \Yii;
use api_web\components\WebApiNoAuthController;
use yii\web\BadRequestHttpException;

/**
 * Class CallbackController
 * @package api_web\controllers
 */
class CallbackController extends WebApiNoAuthController
{
    /**
     * @var array $request
     */
    protected $request;
    /**
     * @var array $response
     */
    protected $response;
    /**
     * @var \yii\di\Container $container
     */
    public $container;

    /**
     * @SWG\Post(path="/callback/load-dictionary",
     *     tags={"Callback"},
     *     summary="Загрузка справочников с помощью коллбека",
     *     description="Загрузка справочников с помощью коллбека",
     *     produces={"application/xml"},
     *     @SWG\Parameter(
     *         name="t",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *             @SWG\Schema(
     *                 default={{}}
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={{}}
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
    public function actionLoadDictionary()
    {
        $task_id = Yii::$app->getRequest()->getQueryParam(AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        if (!$task_id) {
            throw new BadRequestHttpException( \Yii::t('api_web', "empty_param|{param}", ['ru'=>'Неуказан параметр|{param}', 'param' => AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $mcTask = OuterTask::findOne(['inner_guid' => $task_id]);
        if (!$mcTask || $mcTask->int_status_id != OuterTask::STATUS_REQUESTED) {
            throw new BadRequestHttpException( \Yii::t('api_web', "wrong_param|{param}", ['ru'=>'Неверный параметр|{param}', 'param' => AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $this->response = $this->container->get('NoAuthWebApi')->loadDictionary($mcTask);
    }

    /**
     * @SWG\Post(path="/callback/send-waybill",
     *     tags={"Callback"},
     *     summary="Колбек на выгрузку накладной",
     *     description="Колбек на выгрузку накладной",
     *     produces={"application/xml"},
     *     @SWG\Parameter(
     *         name="t",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *             @SWG\Schema(
     *                 default={{}}
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={{}}
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
    public function actionSendWaybill()
    {
        $task_id = Yii::$app->getRequest()->getQueryParam(AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        if (!$task_id) {
            throw new BadRequestHttpException( \Yii::t('api_web', "empty_param|{param}", ['ru'=>'Неуказан параметр|{param}', 'param' => AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $mcTask = OuterTask::findOne(['inner_guid' => $task_id]);
        if (!$mcTask || $mcTask->int_status_id != OuterTask::STATUS_REQUESTED) {
            throw new BadRequestHttpException( \Yii::t('api_web', "wrong_param|{param}", ['ru'=>'Неверный параметр|{param}', 'param' => AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $this->response = $this->container->get('NoAuthWebApi')->sendWaybill($mcTask);
    }

}