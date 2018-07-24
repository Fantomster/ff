<?php

namespace console\controllers;


class WatcherDaemonController extends \vyants\daemon\controllers\WatcherDaemonController
{
    /**
     * Список демонов
     * @var array
     */
    public $daemons= [];

    /**
     * Запускать как демон
     * @var bool
     */
    public $demonize = true;

    /**
     * @var int
     */
    protected $sleep = 5;

    /**
     * Реконнекты
     * @return bool
     */
    protected function renewConnections()
    {
        return true;
    }

    /**
     * @param $className
     * @return mixed
     */
    protected function getCommandNameBy($className)
    {
        return $className;
    }

    /**
     * @return array
     */
    protected function defineJobs()
    {
        sleep($this->sleep);

        if (!empty($this->daemons)) {
            foreach ($this->daemons as $daemon) {
                \Yii::$app->controllerMap[$daemon['className']] = ['class' => 'console\modules\daemons\controllers\\'.$daemon['className']];
            }
        }

        return $this->daemons;
    }
}