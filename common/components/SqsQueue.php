<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

use yii\base\Component;
use Aws\Sqs\SqsClient;
use yii\helpers\Json;

/**
 * Description of SqsQueue
 *
 * @author El Babuino
 */
class SqsQueue extends Component
{
    private $_client;
    
    public $config = [];
    
    public function init()
    {
        $this->_client = SqsClient::factory($this->config);
    }
    
    public function getClient()
    {
        if (empty($this->_client)) {
            $this->init();
        }
        return $this->_client;
    }
    
    /**
     * @param string $queueUrl
     * @param object $message
     * 
     * @return boolean result
     */
    public function sendMessage($queueUrl, $message)
    {
        $result = $this->_client->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => Json::encode($message),
        ]);
//        $result = $this->_client->getCommand('SendMessage',[
//            'QueueUrl' => $queueUrl,
//            'MessageBody' => Json::encode($message),
//        ]);
        return ($result !== null);
    }
}
