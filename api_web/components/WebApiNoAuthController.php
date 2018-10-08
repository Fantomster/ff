<?php

namespace api_web\components;

use api_web\modules\integration\classes\SyncLog;
use \Yii;
use yii\rest\Controller;

/**
 * Class WebApiNoAuth
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
        SyncLog::trace('Initialized init Controller as ' . __METHOD__, WebApiNoAuth::LOG_INDEX);
        SyncLog::trace('Try to initialized Controller->container as Component...');
        $this->container = (new WebApiNoAuth())->container;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $my['contentNegotiator'] = [
            'class' => yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/xml' => yii\web\Response::FORMAT_XML
            ]
        ];
        $behaviors = array_merge($behaviors, $my);
        SyncLog::trace('Setup behaviors: content = application/xml');
        return $behaviors;
    }

    /**
     * @param yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {

        $this->enableCsrfValidation = false;
        SyncLog::trace('Setuped Response CsrfValidation to false');

        Yii::$app->response->headers->add('Access-Control-Allow-Origin', '*');
        Yii::$app->response->headers->add('Access-Control-Allow-Methods', '*');
        SyncLog::trace('Created Response headers: Access-Control-Allow-Origin, Access-Control-Allow-Methods (*)');

        if (parent::beforeAction($action)) {
            Yii::$app->setTimeZone('Etc/GMT');
            SyncLog::trace('Setup time zone');
            return true;
        }
        SyncLog::trace('Error beforeAction');
        return false;
    }

    /**
     * @param yii\base\Action $action
     * @param mixed $result
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
