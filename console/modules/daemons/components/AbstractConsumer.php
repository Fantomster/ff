<?php

/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 18:49
 */

namespace console\modules\daemons\components;

use api\common\models\RabbitQueues;
use yii\helpers\BaseStringHelper;

/**
 * Abstract class AbstractConsumer with realization common methods for consumers
 */
abstract class AbstractConsumer
{
    /** @var integer $timeout in seconds */
    public static $timeout = 300;
    /** @var string $data data from queue message */
    public $data;
    /** @var integer $timeoutExecuting timeout in seconds for execution consumer */
    public static $timeoutExecuting = 600;
    /**
     * @var
     */
    public $logPrefix;

    /**
     * @param array|string $message
     */
    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $message = $message . PHP_EOL;
        $message .= str_pad('', 80, '=') . PHP_EOL;
        $className = BaseStringHelper::basename(get_class($this));
        \Yii::info($className . ": ($this->logPrefix) " . $message);
        if (!\Yii::$app->params['disable_daemon_logs']) {
            file_put_contents(\Yii::$app->basePath . "/runtime/daemons/logs/jobs_" . $className . '.log', $message, FILE_APPEND);
        }
    }

    /**
     * Запрос на постановку в очередь обновлений справочника
     *
     * @param integer $org_id
     */
    public static function getUpdateData($org_id): void
    {
        $arClassName = explode("\\", static::class);
        $className = array_pop($arClassName);
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => $className, 'organization_id' => $org_id])->one();
            if ($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = $className;
                $queue->organization_id = $org_id;
                if ($queue->validate()) {
                    $queue->save();
                }
            }

            $queueName = $queue->consumer_class_name;

            if (!empty($queue->organization_id)) {
                $queueName .= '_' . $queue->organization_id;
            }

            //ставим задачу в очередь
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue(uniqid());

        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }
}
