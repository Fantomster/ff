<?php

namespace api_web\components;

use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\modules\integration\classes\SyncLog;
use common\models\AllServiceOperation;
use common\models\OuterTask;
use \Yii;
use yii\rest\Controller;

/**
 * Class WebApiNoAuth
 *
 * @package api_web\components
 */
class WebApiNoAuthController extends Controller
{

    /**
     * @var array $request
     */
    protected $request;

    /**
     * @var array $request
     */
    protected $content;

    /**
     * @var array $response
     */
    protected $response;

    /**
     * @var yii\di\Container $container
     */
    public $container;

    /**
     * Получаем контейнер
     */
    public function init()
    {
        $task_id = Yii::$app->getRequest()->getQueryParam(AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        if ($task_id) {
            $mcTask = OuterTask::findOne(['inner_guid' => $task_id]);
            if ($task_id) {
                $task_id = $mcTask->id;
            }
        }
        if (!$task_id) {
            $task_id = null;
        }
        SyncLog::trace('Initialized init Controller as ' . __METHOD__, WebApiNoAuth::LOG_INDEX, $task_id);
        $this->container = (new WebApiNoAuth())->container;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $my['contentNegotiator'] = [
            'class'   => yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/xml' => yii\web\Response::FORMAT_XML
            ]
        ];
        $behaviors = array_merge($behaviors, $my);
        return $behaviors;
    }

    /**
     * @param yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        Yii::$app->response->headers->add('Access-Control-Allow-Origin', '*');
        Yii::$app->response->headers->add('Access-Control-Allow-Methods', '*');

        if (parent::beforeAction($action)) {
            Yii::$app->setTimeZone('Etc/GMT');
            return true;
        }
        return false;
    }

    /**
     * @param yii\base\Action $action
     * @param mixed           $result
     * @return array|string
     */
    public function afterAction($action, $result)
    {
        SyncLog::trace('Prepare final response');
        parent::afterAction($action, $result);
        if (!empty($this->response)) {
            return $this->response;
        } else {
            return 'error';
        }
    }

}
