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
    public $queue;          #queue - очередь
    public $exchange = 'router';

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

        //Создаёт совединение с RabbitAMQP
        $connection = new AMQPStreamConnection(
            $this->host,        #host - имя хоста, на котором запущен сервер RabbitMQ
            $this->port,        #port - номер порта сервиса, по умолчанию - 5672
            $this->user,        #user - имя пользователя для соединения с сервером
            $this->password     #password
        );

        //Канал
        $channel = $connection->channel();
        $channel->queue_declare(
            $this->queue,       #queue name - Имя очереди может содержать до 255 байт UTF-8 символов
            false,              #passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
            true,               #durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
            false,              #exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
            false               #autodelete - очередь удаляется, когда отписывается последний подписчик
        );

        //Публикуем сообщение
        $channel->basic_publish(
            new AMQPMessage($message),  #message
            $this->exchange,            #exchange
            $this->queue                #routing key
        );

        //Разрыв соединения
        $channel->close();
        $connection->close();
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
        $this->queue = $queue;
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
}