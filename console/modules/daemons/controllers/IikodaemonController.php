<?php

namespace console\modules\daemons\controllers;

use api_web\modules\integration\modules\iiko\helpers\iikoLogger;
use api_web\modules\integration\modules\iiko\models\iikoService;
use common\models\Journal;
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
        return (\Yii::$app->rabbit->queue_prefix ?? '') . iikoLogger::getNameQueue();
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

            /**
             * Вносим информацию об операции в общий журнал
             */
            $journal = new Journal();
            $journal->user_id = $row['user_id'];
            $journal->organization_id = $row['organization_id'];
            $journal->operation_code = $row['operation_code'];
            $journal->log_guide = $row['guide'];
            $journal->type = $row['type'] ?? 'success';
            $journal->response = (strlen($row['response']) > 200 ? 'Long text' : $row['response']);
            $journal->service_id = iikoService::getServiceId();

            if(!$journal->save()) {
                print_r($journal->getFirstErrors());
            }

            $this->ask($job);
        } else {
            $this->nask($job);
        }
        return true;
    }
}