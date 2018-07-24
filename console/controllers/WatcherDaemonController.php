<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.02.2018
 * Time: 18:30
 */

namespace console\controllers;


use console\modules\daemons\controllers\iikoLogDaemonController;

class WatcherDaemonController extends \vyants\daemon\controllers\WatcherDaemonController
{
    /**
     * @var string subfolder in console/controllers
     */
    public $daemonFolder = 'daemons';

    /**
     * @var boolean flag for first iteration
     */
    protected $firstIteration = true;

    /**
     * @var int
     */
    protected $sleep = 4;

    /**
     * @return bool
     */
    protected function renewConnections() {
        return true;
    }

    /**
     * @return array
     */
    protected function defineJobs()
    {
        sleep($this->sleep);
        return [
            ['className' => iikoLogDaemonController::className(), 'enabled' => true]
        ];
    }
}