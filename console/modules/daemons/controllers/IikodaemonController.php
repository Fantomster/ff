<?php

namespace console\modules\daemons\controllers;

use api_web\modules\integration\modules\iiko\models\iikoService;
use console\modules\daemons\components\AbstractDaemonController;

use PhpAmqpLib\Message\AMQPMessage;

class IikodaemonController extends AbstractDaemonController
{
    /**
     * Имя очереди
     * @return string
     */
    public function getQueueName() {
        return 'log_service_' . iikoService::getServiceId();
    }

    /**
     * Топик обмена
     * @return string
     */
    public function getExchangeName() {
        return 'log';
    }

    /**
     * Обработка полученных сообщений
     * @param AMQPMessage $job
     * @return bool
     */
    public function doJob($job)
    {
        $this->ask($job);
        echo $job->body;
        return true;
    }
}