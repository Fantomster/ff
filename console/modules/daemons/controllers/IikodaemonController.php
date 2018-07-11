<?php

namespace console\modules\daemons\controllers;

use api_web\modules\integration\modules\iiko\helpers\iikoLogger;
use api_web\modules\integration\modules\iiko\models\iikoService;
use console\modules\daemons\components\AbstractDaemonController;

use PhpAmqpLib\Message\AMQPMessage;

class IikodaemonController extends AbstractDaemonController
{
    /**
     * Имя очереди
     * @return string
     */
    public function getQueueName()
    {
        return 'log_service_' . iikoService::getServiceId();
    }

    /**
     * Топик обмена
     * @return string
     */
    public function getExchangeName()
    {
        return 'log';
    }

    /**
     * Обработка полученных сообщений
     * @param AMQPMessage $job
     * @return bool
     */
    public function doJob($job)
    {
        $row = \json_decode($job->body, true);
        if (\Yii::$app->get('db_api')->createCommand()->insert(iikoLogger::$tableName, $row)->execute()) {
            $this->ask($job);
        } else {
            $this->nask($job);
        }
        return true;
    }
}