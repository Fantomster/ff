<?php

/**
 * Class WebApiNoAuth
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-04
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\components;

use Yii;
use yii\di\Container;
use dosamigos\resourcemanager\ResourceManagerInterface;
use common\components\resourcemanager\AmazonS3ResourceManager;
use api_web\modules\integration\classes\SyncLog;
use api_web\classes\NoAuthWebApi;

/**
 * Class WebApiNoAuth
 * @package api_web\components
 */
class WebApiNoAuth
{
    /** @var $container Container */
    public $container;

    /** @var $resourceManager ResourceManagerInterface */
    public $resourceManager;

    /** @const Индекс лога */
    const LOG_INDEX = 'no_auth';

    function __construct()
    {
        SyncLog::trace('Initialized Component as ' . self::class);
        $this->getContainerClasses();
        $this->resourceManager = \Yii::$app->get('resourceManager');
        /** @var AmazonS3ResourceManager $resource */
        SyncLog::trace('Successfully initialized Component->resourceManager as ' .
            AmazonS3ResourceManager::class);
    }

    /**
     * @return mixed|Container
     */
    private function getContainerClasses()
    {
        if (!$this->container) {
            $this->container = new Container();
            $classes = array_filter(scandir(Yii::getAlias('@api_web/classes/')), function ($name) {
                return strstr($name, 'NoAuthWebApi.php');
            });
            foreach ($classes as $file) {
                $class = basename($file, '.php');
                $this->container->set($class, '\api_web\classes\\' . $class);
            }
            SyncLog::trace('Successfully initialized Component->container as ' . NoAuthWebApi::class);
        }
        return $this->container;
    }
}