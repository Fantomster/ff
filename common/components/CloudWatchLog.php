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

    const NEW_STREAM = 'new';
    
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

    private function groupExists($groupName, $logGroups)
    {
        foreach ($logGroups as $logGroup) {
            if ($logGroup['logGroupName'] == $groupName) {
                return true;
            }
        }
        return false;
    }

    private function checkGroup($groupName)
    {
        try {
            $client = $this->getClient();

            $result = $client->describeLogGroups([
                'limit' => 10,
                'logGroupNamePrefix' => $groupName,
            ]);

            if ($result['@metadata']['statusCode'] != 200) {
                return false;
            }

            if (!$this->groupExists($groupName, $result['logGroups'])) {
                $result = $client->createLogGroup([
                    'logGroupName' => $groupName,
                ]);
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return false;
        }
    }

    private function streamExists($streamName, $logStreams)
    {
        foreach ($logStreams as $logStream) {
            if ($logStream['logStreamName'] == $streamName) {
                return isset($logStream['uploadSequenceToken']) ? $logStream['uploadSequenceToken'] : self::NEW_STREAM;
            }
        }
        return null;
    }

    private function getStreamToken($groupName, $streamName)
    {
        $client = $this->getClient();

        if (!$this->checkGroup($groupName)) {
            throw new \Exception('Checking log group failed!');
        }

        $result = $client->describeLogStreams([
            'limit' => 10,
            'logGroupName' => $groupName,
            'logStreamNamePrefix' => $streamName,
        ]);

        if ($result['@metadata']['statusCode'] != 200) {
            throw new \Exception('Checking log stream failed!');
        }

        $result = $this->streamExists($streamName, $result['logStreams']);
        if (empty($result)) {
            $result = $client->createLogStream([
                'logGroupName' => $groupName,
                'logStreamName' => $streamName,
            ]);
        } elseif ($result != self::NEW_STREAM) {
            return $result;
        }
        return null;
    }

    public function writeLog($groupName, $streamName, $message)
    {
        $client = $this->getClient();

        $token = $this->getStreamToken($groupName, $streamName);

        try {
            $request = [
                'logGroupName' => $groupName,
                'logStreamName' => $streamName,
                'logEvents' => [
                    [
                        'message' => $message,
                        'timestamp' => round(microtime(true) * 1000),
                    ]
                ],
            ];
            if (!empty($token)) {
                $request['sequenceToken'] = $token;
            }
            $client->putLogEvents($request);
            return true;
        } catch (\Throwable $e) {
            \Yii::error($e->getCode() . PHP_EOL . $e->getMessage() .  PHP_EOL . $e->getTraceAsString());
            return false;
        }
    }

}
