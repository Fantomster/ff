<?php

namespace console\modules\daemons\components;

use api\common\models\RabbitQueues;
use api_web\components\FireBase;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use yii\db\Expression;
use yii\db\Query;

abstract class AbstractDaemonController extends DaemonController
{

    /**
     * @var \console\modules\daemons\components\ConsumerInterface
     */
    public $consumer;

    /**
     * Description
     * @var RabbitService
     */
    private $rabbit;
    /**
     * Description
     * @var AMQPStreamConnection
     */
    private $connect;
    /**
     * Description
     * @var AMQPChannel
     */
    private $channel = null;

    /**
     * @var int
     */
    public $maxChildProcesses = 5;

    /**
     * last_executed consumer time
     * @var \DateTime
     * */
    public $lastExec = null;

    /**
     * Check consumer implements interfaces methods
     * @param \console\modules\daemons\components\ConsumerInterface $consumer
     */
    private function getConsumer(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Generate class string
     * @return string
     */
    public function getConsumerClassName()
    {
        return "console\modules\daemons\classes\\" . $this->consumerClass;
    }

    /**
     * Create consumer with different parameters
     * maybe refactoring to argument unpacking new class(...$arrayOfConstructorParameters)
     */
    public function createConsumer()
    {
        $arWhere = [
            'consumer_class_name' => $this->consumerClass
        ];

        if (!empty($this->orgId)) {
            $this->getConsumer(new $this->consumerClassName($this->orgId));
            $arWhere['organization_id'] = $this->orgId;
        } else {
            $this->getConsumer(new $this->consumerClassName);
        }

        $dateTime = new \DateTime();
        (new Query())->createCommand(\Yii::$app->db_api)->update(RabbitQueues::tableName(), [
            'start_executing' => $dateTime->format('Y-m-d H:i:s')
        ], $arWhere)->execute();
    }

    public function loggingExecutedTime()
    {
        $arWhere = [
            'consumer_class_name' => $this->consumerClass
        ];

        if (!empty($this->orgId)) {
            $arWhere['organization_id'] = $this->orgId;
        }

        $dateTime = new \DateTime();
        $this->lastExec = $dateTime->format('Y-m-d H:i:s');
        (new Query())->createCommand(\Yii::$app->db_api)->update(RabbitQueues::tableName(), [
            'start_executing' => new Expression('NULL'),
            'last_executed'   => $this->lastExec
        ], $arWhere)->execute();
    }

    /**
     * @return array|bool
     */
    protected function defineJobs()
    {
        $this->rabbit = \Yii::$app->get('rabbit');
        $consumerTag = get_class($this);

        //Получаем канал, если нет, создаем
        $channel = $this->getChannel($this->getQueueName(), $this->getExchangeName());
        //Получение сообщений из очереди по одному
        $channel->basic_qos(null, 1, null);
        //Цепляем канал к очереди
        $channel->queue_bind($this->getQueueName(), $this->getExchangeName(), $this->getQueueName());
        //Цепляем консьюмера
        $channel->basic_consume($this->getQueueName(), $consumerTag, false, false, false, false, [$this, 'doJob']);

        while (count($channel->callbacks)) {
            try {
                $channel->wait(null, true, 5);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $timeout) {
            } catch (\PhpAmqpLib\Exception\AMQPRuntimeException $runtime) {
                \Yii::error($runtime->getMessage());
            }
        }
        return false;
    }

    /**
     * @param $message array|string
     */
    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $message = $message . PHP_EOL;
        $message .= str_pad('', 80, '=') . PHP_EOL;
        \Yii::info(self::shortClassName().": ".$message);
        file_put_contents(\Yii::$app->basePath . "/runtime/daemons/logs/jobs_" . self::shortClassName() . '.log', $message, FILE_APPEND);
    }

    /**
     * Поддержка соединений
     */
    public function renewConnections()
    {
        //if (\Yii::$app->db->isActive) {
        \Yii::$app->db->close();
        \Yii::$app->db->open();
        //}

        //if (\Yii::$app->db_api->isActive) {
        \Yii::$app->db_api->close();
        \Yii::$app->db_api->open();
        //}
    }

    /**
     * send to FCM when consumer complete work
     * */
    public function noticeToFCM(): void
    {
        $arFB = [
            'dictionaries',
            'queue' => $this->queueName,
        ];

        $count = \Yii::$app->get('rabbit')->setQueue($this->queueName)->checkQueueCount();

        FireBase::getInstance()->update($arFB, [
            'last_executed'  => $this->lastExec,
            'plain_executed' => $this->lastTimeout,
            'count'          => $count,
        ]);
    }

    /**
     * Get last timeout from last exec time
     * @return string|null
     */
    public function getLastTimeout()
    {
        if (!is_null($this->lastExec)) {
            $lastExec = new \DateTime($this->lastExec);
            $timeOut = $lastExec->getTimestamp() + $this->consumerClassName::$timeout;
            return date('Y-m-d H:i:s', $timeOut);
        }
        return null;
    }

    /**
     * @param        $queue
     * @param string $exchange
     * @return AMQPChannel
     */
    protected function getChannel($queue, $exchange = '')
    {
        if ($this->channel === null) {
            if ($this->connect == null) {
                $this->connect = $this->rabbit->connect();
            }
            $this->channel = $this->connect->channel();
            $this->channel->exchange_declare($exchange, 'direct', false, true, false);
            $this->channel->queue_declare($queue, false, true, false, false);
        }
        return $this->channel;
    }

    /**
     * @param $job AMQPMessage
     */
    protected function ask($job)
    {
        $this->channel->basic_ack($job->delivery_info['delivery_tag']);
    }

    /**
     * @param $job AMQPMessage
     */
    protected function nask($job)
    {
        $this->channel->basic_nack($job->delivery_info['delivery_tag'], false, false);
    }

    /**
     * @param $job AMQPMessage
     */
    protected function cancel($job)
    {
        $this->channel->basic_cancel($job->delivery_info['consumer_tag']);
    }

    /**
     * Exchange name
     * @return string
     */
    protected function getExchangeName()
    {
        return 'amq.direct';
    }

    /**
     * Queue name
     * @return string
     */
    abstract protected function getQueueName();

}