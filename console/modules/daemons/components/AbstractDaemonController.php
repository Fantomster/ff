<?php

namespace console\modules\daemons\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use vyants\daemon\DaemonController;
use PhpAmqpLib\Channel\AMQPChannel;

abstract class AbstractDaemonController extends DaemonController
{
    private $connect;
    private $channel = null;

    /**
     * @param $queue
     * @param string $exchange
     * @return AMQPChannel
     */
    protected function getChannel($queue, $exchange = '')
    {
        if ($this->channel == null) {
            /**
             * @var $rabbit AMQPStreamConnection
             */
            if ($this->connect == null) {
                $rabbit = $this->connect = \Yii::$app->get('rabbit')->connect();
            }

            if ($rabbit->channel() == null) {
                $rabbit->channel()->exchange_declare($exchange, 'direct', false, true, false);
                echo "Daemon create chanel" . PHP_EOL;
            }
            $this->channel = $rabbit->channel();
            list($queue, ,) = $this->chanel->queue_declare($queue, false, true, false, false);
            $this->channel->queue_bind($queue, $exchange, $queue);
            $consumerTag = get_class($this) . '_consumer';
            $this->channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'doJob']);
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
}