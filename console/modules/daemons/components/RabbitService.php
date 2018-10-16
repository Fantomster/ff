<?php

namespace console\modules\daemons\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Component;
use yii\base\ErrorException;

class RabbitService extends Component
{

    public $host;           #host - имя хоста, на котором запущен сервер RabbitMQ
    public $port = 5672;    #port - номер порта сервиса, по умолчанию - 5672
    public $user;           #user - имя пользователя для соединения с сервером
    public $password;       #password
    public $queue = 'empty';          #queue - очередь
    public $exchange = 'amq.direct';
    public $queue_prefix = '';
    public $vhost = '/';

    public function addRabbitQueue($message)
    {
        //Должна быть указана очередь
        if ($this->queue == null) {
            throw new ErrorException('Необходимо установить имя очереди (new RabbitService())->setQueue($name_queue);');
        }

        //Если массив, превратим его в JSON
        if (is_array($message)) {
            $message = json_encode($message);
        }

        $connection = $this->connect();
        $channel = $connection->channel();

        $channel->queue_declare(
                $this->queue, #queue name - Имя очереди может содержать до 255 байт UTF-8 символов
                false, #passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
                true, #durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
                false, #exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
                false               #autodelete - очередь удаляется, когда отписывается последний подписчик
        );

        $channel->queue_bind($this->queue, $this->exchange, $this->queue);

        //Публикуем сообщение
        $channel->basic_publish(
                new AMQPMessage($message), #message
                $this->exchange, #exchange
                $this->queue                #routing key
        );

        try {
            $clientIp = isset(Yii::$app->request->userIP) ? Yii::$app->request->userIP : "undefined";
            $requestUrl = isset(Yii::$app->request->url) ? Yii::$app->request->url : "undefined";
            $logMessage = "client ip: " . $clientIp . "; request url: " . $requestUrl;
            \Yii::$app->get('cloudWatchLog')->writeLog(Yii::$app->params['rabbitLogGroup'], $this->queue, $logMessage);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }

        //Разрыв соединения
        $channel->close();
        $connection->close();
    }

    /**
     * Check number of jobs and consumers in queue
     * @throws ErrorException
     * @return array
     * */
    public function checkQueueCount()
    {
        //Должна быть указана очередь
        if ($this->queue == null) {
            throw new ErrorException('Необходимо установить имя очереди (new RabbitService())->setQueue($name_queue);');
        }

        $connection = $this->connect();
        $channel = $connection->channel();
        list($queue, $messageCount, $consumerCount) = $channel->queue_declare($this->queue, true);

        //Разрыв соединения
        $channel->close();
        $connection->close();

        return [
            'count' => $messageCount,
            'consumerCount' => $consumerCount,
        ];
    }

    /**
     * Название очереди
     * @param $queue
     * @return $this
     * @throws ErrorException
     */
    public function setQueue($queue)
    {
        if (empty($queue)) {
            throw new ErrorException('Необходимо установить имя очереди (new RabbitService())->setQueue($name_queue);');
        }
        $this->queue = ($this->queue_prefix ?? '') . $queue;
        return $this;
    }

    /**
     * @param $exchange
     * @return $this
     */
    public function setExchange($exchange)
    {
        if (empty($exchange)) {
            $this->exchange = '';
        }
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function connect()
    {
        return new AMQPStreamConnection(
                $this->host, #host - имя хоста, на котором запущен сервер RabbitMQ
                $this->port, #port - номер порта сервиса, по умолчанию - 5672
                $this->user, #user - имя пользователя для соединения с сервером
                $this->password, #password
                $this->vhost
        );
    }

}
