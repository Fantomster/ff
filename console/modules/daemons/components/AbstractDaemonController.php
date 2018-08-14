<?php

namespace console\modules\daemons\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use vyants\daemon\DaemonController;
use PhpAmqpLib\Channel\AMQPChannel;

abstract class AbstractDaemonController extends DaemonController
{
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
     * @return array|bool
     */
    protected function defineJobs()
    {
        $this->rabbit = \Yii::$app->get('rabbit');
        $consumerTag = get_class($this);

        //Получаем канал, если нет, создаем
        $channel = $this->getChannel($this->getQueueName(), $this->getExchangeName());
        //Цепляем канал к очереди
        $channel->queue_bind($this->getQueueName(), $this->getExchangeName(), $this->getQueueName());
        //Цепляем консьюмера
        $channel->basic_consume($this->getQueueName(), $consumerTag, false, false, false, false, [$this, 'doJob']);

        /**
         * Инофрмация о подключении
         */
        \Yii::trace("HOST: " . $this->rabbit->host . PHP_EOL);
        \Yii::trace("V_HOST: " . $this->rabbit->vhost . PHP_EOL);
        \Yii::trace("Exchange: " . $this->getExchangeName() . PHP_EOL);
        \Yii::trace("Queue: " . $this->getQueueName() . PHP_EOL);
        \Yii::trace("Consumer: " . $consumerTag . PHP_EOL);

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
     * Поддержка соединений
     */
    protected function renewConnections()
    {
        if (isset(\Yii::$app->db_api)) {
            \Yii::$app->db_api->close();
            \Yii::$app->db_api->open();
        }
        if (isset(\Yii::$app->db)) {
            \Yii::$app->db->close();
            \Yii::$app->db->open();
        }
    }

    /**
     * @param $queue
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
        $job->delivery_info['channel']->basic_ack($job->delivery_info['delivery_tag']);
    }

    /**
     * @param $job AMQPMessage
     */
    protected function nask($job)
    {
        $job->delivery_info['channel']->basic_nack($job->delivery_info['delivery_tag']);
    }

    /**
     * @param $job AMQPMessage
     */
    protected function cancel($job)
    {
        $job->delivery_info['channel']->basic_cancel($job->delivery_info['consumer_tag']);
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