<?php

namespace console\modules\daemons\controllers;

use api_web\components\Registry;
use api_web\helpers\TillypadLogger;
use common\models\Journal;
use console\modules\daemons\components\AbstractDaemonController;

class TillypadLogDaemonController extends AbstractDaemonController
{
    /**
     * Имя очереди
     *
     * @return string
     */
    public function getQueueName()
    {
        return TillypadLogger::getNameQueue();
    }

    /**
     * Обработка полученных сообщений
     *
     * @param $job
     * @return bool
     */
    public function doJob($job)
    {
        $this->renewConnections();

        $row = \json_decode($job->body, true);
        try {
            if (!is_array($row)) {
                throw new \Exception('Message is not array! ' . PHP_EOL . print_r($row, true));
            }

            try {
                \Yii::$app->get('db_api')->createCommand()->insert(TillypadLogger::$tableName, $row)->execute();
            } catch (\Exception $e) {
                $this->log(PHP_EOL . " DIE: HALT " . $e->getMessage());
                @unlink(\Yii::$app->basePath . "/runtime/daemons/pids/" . self::shortClassName());
                die('die mysql connection');
            }

            /**
             * Вносим информацию об операции в общий журнал
             */
            $journal = new Journal();
            $journal->user_id = $row['user_id'] ?? null;
            $journal->organization_id = $row['organization_id'] ?? null;
            $journal->operation_code = $row['operation_code'];
            $journal->log_guide = $row['guide'];
            $journal->type = $row['type'] ?? 'success';
            $journal->response = (strlen($row['response']) > 200 ? 'Long text' : $row['response']);
            $journal->service_id = Registry::TILLYPAD_SERVICE_ID;
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