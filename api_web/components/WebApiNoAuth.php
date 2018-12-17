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

/**
 * Class WebApiNoAuth
 * @package api_web\components
 */
class WebApiNoAuth
{
    /** @var $container Container */
    public $container;

    /** @var $resourceManager \common\components\resourcemanager\AmazonS3ResourceManager */
    public $resourceManager;

    /** @const Индекс лога */
    const LOG_INDEX = 'no_auth';

    function __construct()
    {
        $this->getContainerClasses();
        $this->resourceManager = \Yii::$app->get('resourceManager');
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
        }
        return $this->container;
    }
}