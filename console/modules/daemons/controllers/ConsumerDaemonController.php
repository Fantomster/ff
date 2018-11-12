<?php

namespace console\modules\daemons\controllers;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\AbstractDaemonController;
use yii\db\Expression;
use yii\db\Query;

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
        $this->initLogger();
        $this->renewConnections();

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

            if ($success) {
                $this->ask($job);
            } else {
                throw new \Exception('$success false');
            }
        } catch (\Throwable $e) {
            $this->cancel($job);
            $arWhere = ['consumer_class_name' => $this->consumerClass];
            if (!empty($this->orgId)) {
                $arWhere['organization_id'] = $this->orgId;
            }
            (new Query())->createCommand(\Yii::$app->db_api)->update(RabbitQueues::tableName(), [
                'start_executing' => new Expression('NULL')
            ], $arWhere)->execute();
            $this->log(PHP_EOL . " ERROR: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return true;
    }
}