<?php

namespace console\modules\daemons\controllers;

use console\modules\daemons\components\AbstractDaemonController;

class ConsumerDaemonController extends AbstractDaemonController
{
    /**
     * Имя очереди
     * @return string
     */
    public function getQueueName()
    {
        if (!is_null($this->orgId)) {
            return $this->consumerClass . '_' . $this->orgId;
        }
        return $this->consumerClass;
    }
    
    /**
     * Обработка полученных сообщений
     * @param $job
     * @return bool
     */
    public function doJob($job)
    {
        if(!is_null($this->lastExec)){
            $lastExec = new \DateTime($this->lastExec);
            $timeOut = $lastExec->getTimestamp() + $this->consumerClassName::$timeout;
        }
        
        $this->renewConnections();
//        $row = \json_decode($job->body, true);
        
        try {
            if (!is_null($this->lastExec) && date('Y-m-d H:i:s', $timeOut) > date('Y-m-d H:i:s')) {
                $this->log(PHP_EOL . " ERROR: " . 'timeout > date');
                $success = true;
            } else {
                $this->createConsumer();
                $this->consumer->data = $job->body;
                $this->consumer->getData();
                $success = $this->consumer->saveData();
                $this->log(PHP_EOL . " ERROR: " . 'timeout < date');
                $this->loggingExecutedTime();
            }
//            if (!is_array($row)) {
//                throw new \Exception('Message is not array! ' . PHP_EOL . print_r($row, true));
//            }

//            try{
//                \Yii::$app->get('db_api')->createCommand()->insert(iikoLogger::$tableName, $row)->execute();
//            } catch (\Exception $e) {
//                $this->log(PHP_EOL . " DIE: HALT " . $e->getMessage());
//                @unlink(\Yii::$app->basePath . "/runtime/daemons/pids/" . self::shortClassName());
//                die('die mysql connection');
//            }
            if ($success) {
                $this->ask($job);
            } else {
                throw new \Exception('$success false');
            }
        } catch (\Exception $e) {
            $this->log(PHP_EOL . " ERROR: " . $e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->nask($job);
        }
        return true;
    }
}