<?php

namespace api_web\components;

/**
 * Class WebApi
 * @package api_web\components
 */
class WebApi
{
    /**
     * @var \yii\di\Container
     */
    public $container;
    /**
     * @var \common\models\User
     */
    public $user;
    /**
     * @var \dosamigos\resourcemanager\ResourceManagerInterface 
     */
    public $resourceManager;
    
    function __construct()
    {
        $this->getContainerClasses();
        $this->getUser();
        $this->resourceManager = \Yii::$app->get('resourceManager');
    }

    /**
     * @return \common\models\User
     */
    public function getUser()
    {
        if (empty($this->user)) {
            $this->user = \common\models\User::findOne(\Yii::$app->user->getId());
        }
        return $this->user;
    }
    
    /**
     * @return mixed|\yii\di\Container
     */
    private function getContainerClasses()
    {
        if (!$this->container) {
            $this->container = new \yii\di\Container();

            $classes = array_filter(scandir(\Yii::getAlias('@api_web/classes/')), function ($name) {
                return strstr($name, 'WebApi.php');
            });

            foreach ($classes as $file) {
                $class = basename($file, '.php');
                $this->container->set($class, '\api_web\classes\\' . $class);
            }
        }
        return $this->container;
    }
}