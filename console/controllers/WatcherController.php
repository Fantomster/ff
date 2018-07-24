<?php

namespace console\controllers;

class WatcherController extends \vyants\daemon\controllers\WatcherDaemonController
{
    protected $sleep = 5;

    public $demonize = true;

    public $daemonsList = [
        [
            'className' => 'iikoLogDaemonController',
            'enabled' => true
        ]
    ];

    protected function renewConnections()
    {
    }

    protected function getCommandNameBy($className)
    {
        return $className;
    }

    protected function defineJobs()
    {
        sleep($this->sleep);
        if (!empty($this->daemonsList)) {
            foreach ($this->daemonsList as $daemon) {
                \Yii::$app->controllerMap[$daemon['className']] = ['class' => 'console\modules\daemons\controllers\\'.$daemon['className']];
            }
        }
        return $this->daemonsList;
    }
}