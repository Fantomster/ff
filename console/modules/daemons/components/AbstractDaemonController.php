<?php

namespace console\modules\daemons\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

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
     * rabbit queues table name
     * @var \DateTime
     * */
    public $lastExec= null;
    
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
        if (!is_null($this->orgId)) {
            $this->getConsumer(new $this->consumerClassName($this->orgId));
        } else {
            $this->getConsumer(new $this->consumerClassName);
        }
        $dateTime = new \DateTime();
        \Yii::$app->db_api->createCommand('UPDATE rabbit_queues SET start_executing=:datetime WHERE consumer_class_name=:consumerCN AND organization_id=:orgId',
            [ ':consumerCN' => $this->consumerClass,
              ':orgId' => $this->orgId,
              ':datetime' => $dateTime->format('Y-m-d H:i:s')
            ]
        )->execute();
    }
    
    public function loggingExecutedTime(){
        $dateTime = new \DateTime();
        $this->lastExec = $dateTime->format('Y-m-d H:i:s');
        \Yii::$app->db_api->createCommand('UPDATE rabbit_queues SET start_executing=NULL, last_executed=:datetime WHERE consumer_class_name=:consumerCN AND organization_id=:orgId',
            [ ':consumerCN' => $this->consumerClass,
              ':orgId' => $this->orgId,
              ':datetime' => $this->lastExec,
            ]
        )->execute();
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
        //Цепляем канал к очереди
        $channel->queue_bind($this->getQueueName(), $this->getExchangeName(), $this->getQueueName());
        //Цепляем консьюмера
        $channel->basic_consume($this->getQueueName(), $consumerTag, false, false, false, false, [$this, 'doJob']);
        
        /**
         * Инофрмация о подключении
         */
        $this->log([
            "HOST"     => $this->rabbit->host,
            "V_HOST"   => $this->rabbit->vhost,
            "Exchange" => $this->getExchangeName(),
            "Queue"    => $this->getQueueName(),
            "Consumer" => $consumerTag
        ]);
        
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