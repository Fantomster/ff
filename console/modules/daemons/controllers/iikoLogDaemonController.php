<?php

namespace console\modules\daemons\controllers;

use api_web\modules\integration\modules\iiko\helpers\iikoLogger;
use api_web\modules\integration\modules\iiko\models\iikoService;
use common\models\Journal;
use console\modules\daemons\components\AbstractDaemonController;

use PhpAmqpLib\Message\AMQPMessage;

class iikoLogDaemonController extends AbstractDaemonController
{
    /**
     * Имя очереди
     * @return string
     */
    public function getQueueName()
    {
        return iikoLogger::getNameQueue();
    }

    /**
     * Обработка полученных сообщений
     * @param $job
     * @return bool
     */
    public function doJob($job)
    {
        $row = \json_decode($job->body, true);
        try {
            if (!is_array($row)) {
                throw new \Exception('Message is not array! ' . PHP_EOL . print_r($row, true));
            }

            \Yii::$app->get('db_api')->createCommand()->insert(iikoLogger::$tableName, $row)->execute();
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
            if (!$journal->save()) {
                $this->log($journal->getFirstErrors());
            }
            $this->ask($job);
        } catch (\Exception $e) {
            $this->log(PHP_EOL . " ERROR: " . $e->getMessage());
            $this->nask($job);
        }
        return true;
    }
}