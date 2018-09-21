<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

use yii\base\Component;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

/**
 * Description of CloudWatchLog
 *
 * @author El Babuino
 */
class CloudWatchLog extends Component
{
    private $_client;
    
    public $config = [];
    
    public function init()
    {
        $this->_client = CloudWatchLogsClient::factory($this->config);
    }
    
    public function getClient()
    {
        if (empty($this->_client)) {
            $this->init();
        }
        return $this->_client;
    }
    
    public function writeLog($groupName, $streamName)
    {
        //
    }
}
