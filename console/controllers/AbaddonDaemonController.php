<?php

namespace console\controllers;


class AbaddonDaemonController extends \vyants\daemon\controllers\WatcherDaemonController
{
    /**
     * Список демонов
     * @var array
     */
    public $daemons = [];

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
        if (isset(\Yii::$app->db_api)) {
            \Yii::$app->db_api->close();
            \Yii::$app->db_api->open();
        }
        if (isset(\Yii::$app->db)) {
            \Yii::$app->db->close();
            \Yii::$app->db->open();
        }
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
        $queues = \Yii::$app->get('rabbit')->getQueues();
        
        
        exit();
//        if (!empty($this->daemons)) {
//            foreach ($this->daemons as $daemon) {
//                \Yii::$app->controllerMap[$daemon['className']] = ['class' => 'console\modules\daemons\controllers\\' . $daemon['className']];
//            }
//        }

        return $this->daemons;
    }
}