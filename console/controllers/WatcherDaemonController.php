<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.02.2018
 * Time: 18:30
 */

namespace console\controllers;


class WatcherDaemonController extends \vyants\daemon\controllers\WatcherDaemonController
{

    public $daemons= [];
    /**
     * @return array
     */
    protected function defineJobs()
    {
        sleep($this->sleep);
        //TODO: modify list, or get it from config, it does not matter
        /*$daemons = [
            ['className' => 'OneDaemonController', 'enabled' => true],
            ['className' => 'AnotherDaemonController', 'enabled' => false]
        ];*/
        return $this->daemons;
    }
}