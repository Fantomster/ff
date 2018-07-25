<?php

namespace console\modules\daemons\controllers;

use vyants\daemon\DaemonController;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class SomeRabbitQueueController
 */
class RkwsdaemonController extends DaemonController
{
    public $host = '91.239.26.33';      #host - имя хоста, на котором запущен сервер RabbitMQ
    public $port = 5672;                #port - номер порта сервиса, по умолчанию - 5672
    public $user = 'guest';              #user - имя пользователя для соединения с сервером
    public $password = 'guest';          #password
    public $queue = 'deductions';          #queue - очередь

    /**
     *  @var $connection AMQPStreamConnection
     */
    protected $connection;

    /**
     *  @var $connection AMQPChannel
     */
    protected $channel;


    /**
     * @return array|bool
     */
    protected function defineJobs()
    {
        \Yii::trace('Daemon rkws running and working fine');
        echo "Daemon rkws job running and working fine".PHP_EOL;
        $channel = $this->getQueue();
        while (count($channel->callbacks)) {
            try {
                $channel->wait(null, true, 5);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $timeout) {

            } catch (\PhpAmqpLib\Exception\AMQPRuntimeException $runtime) {
                \Yii::error($runtime->getMessage());
                $this->channel = null;
                $this->connection = null;
            }
        }
        return false;
    }

    /**
     * @param AMQPMessage $job
     * @return bool
     * @throws NotSupportedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function doJob($job)
    {
        \Yii::trace('Daemon rkws amqp job stop');
        echo "Daemon rkws job running and working fine".PHP_EOL;
        if ($job->body === 'quit') {
            $this->ask($job);
            $this->cancel($job);
            return true;
            \Yii::trace('Daemon rkws amqp close chanel');
            echo "Daemon rkws close chanel".PHP_EOL;
        }

        $result =  \Yii::$app->rkwsmq->process_rkws($job);

        if ($result) {
            $this->ask($job);
        } else {
            $this->nask($job);
        }

        \Yii::trace('Daemon rkws job stop');
        echo "Daemon rkws job stop".PHP_EOL;
        return $result;
    }

    /**
     * @return AMQPChannel
     * @throws InvalidParamException
     */
    protected function getQueue()
    {
        \Yii::trace('Daemon rkws get queue');
        echo "Daemon rkws get queue".PHP_EOL;
        $exchange = 'router';
        $queue = $this->queue;
        $consumerTag = 'consumer';
        if ($this->channel == null) {
            if ($this->connection == null) {
                /*if (isset(\Yii::$app->params['rabbit'])) {
                    $rabbit = \Yii::$app->params['rabbit'];
                } else {
                    throw new InvalidParamException('Bad config RabbitMQ');
                }*/

                //Establish connection AMQP
                $this->connection = new AMQPStreamConnection(
                    $this->host,    #host - имя хоста, на котором запущен сервер RabbitMQ
                    $this->port,        #port - номер порта сервиса, по умолчанию - 5672
                    $this->user,        #user - имя пользователя для соединения с сервером
                    $this->password        #password
                );

                //$this->connection = new AMQPStreamConnection($rabbit['host'], $rabbit['port'], $rabbit['user'], $rabbit['password']);
            }

            $this->channel = $this->connection->channel();

            $this->channel->exchange_declare($exchange,'direct',false,true,false);

            $args = [];


            list($queue, ,) = $this->channel->queue_declare($queue, false, true, false, false);

           /* foreach ($this->binding_keys as $binding_key) {
                $this->channel->queue_bind($queue, $exchange, $binding_key);
            }*/

            $this->channel->queue_bind($queue,$exchange,$this->queue);

            $this->channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'doJob']);
           // $channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'process_message']);
        }

        \Yii::trace('Daemon rkws create chanel');
        echo "Daemon rkws create chanel".PHP_EOL;
        return $this->channel;
    }


    /**
     * @param $job
     */
    protected function ask($job)
    {
        $job->delivery_info['channel']->basic_ack($job->delivery_info['delivery_tag']);
    }

    /**
     * @param $job
     */
    protected function nask($job)
    {
        $job->delivery_info['channel']->basic_nack($job->delivery_info['delivery_tag']);
    }

    protected function cancel($job)
    {
        $job->delivery_info['channel']->basic_cancel($job->delivery_info['consumer_tag']);
    }

}