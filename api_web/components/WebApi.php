<?php

namespace api_web\components;

use yii\web\BadRequestHttpException;

/**
 * Class WebApi
 *
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
     * @var \common\components\resourcemanager\AmazonS3ResourceManager
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
            $this->user = \common\models\User::find()->where(['id' => \Yii::$app->user->getId()])->one();
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

    /**
     * Check of array $params in $request if not set or empty throw BadRequestHttpException
     * Usage: $this->validateRequest($request, ['service_id', 'id', 'value']);
     *
     * @param       $request
     * @param array $params
     * @throws BadRequestHttpException
     */
    protected function validateRequest($request, $params = [])
    {
        foreach ($params as $param) {
            if (!isset($request[$param]) || empty($request[$param])) {
                throw new BadRequestHttpException(\Yii::t('api_web', "empty_param|{param}", ['ru'=>'Неуказан параметр|{param}', 'param' => $param]));
            }
        }
    }
}