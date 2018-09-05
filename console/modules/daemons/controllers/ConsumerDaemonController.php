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
        if (!empty($this->orgId)) {
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
        $this->renewConnections();
        $this->ask($job);
        
        try {
            if (!is_null($this->lastExec) && $this->lastTimeout > date('Y-m-d H:i:s')) {
                $success = true;
            } else {
                $this->createConsumer();
                $this->consumer->data = $job->body;
                $this->consumer->getData();
                $success = $this->consumer->saveData();
                $this->loggingExecutedTime();
                $this->noticeToFCM();
            }

            if (!$success) {
                throw new \Exception('$success false');
            }
        } catch (\Throwable $e) {
            $this->log(PHP_EOL . " ERROR: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->nask($job);
        }
        return true;
    }
}