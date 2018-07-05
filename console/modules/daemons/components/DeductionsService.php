<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 18.02.2018
 * Time: 21:48
 */
namespace console\modules\daemons\components;

use common\models\commerce\StoreDeduction;
use yii\base\Component;
use yii\db\Exception;

class DeductionsService extends Component
{
    public $host = '91.239.26.33';      #host - имя хоста, на котором запущен сервер RabbitMQ
    public $port = 5672;                #port - номер порта сервиса, по умолчанию - 5672
    public $user = 'guest';              #user - имя пользователя для соединения с сервером
    public $password = 'guest';          #password
    public $queue = 'deductions';          #queue - очередь

    public function addRabbitQueue($id)
    {
        /**
         * Отправляет сообщение в очередь newMails
         *
         * @param string $message
         */
        /**
         * Создаёт совединение с RabbitAMQP
         */
        $connection = new AMQPStreamConnection(
            $this->host,        #host - имя хоста, на котором запущен сервер RabbitMQ
            $this->port,        #port - номер порта сервиса, по умолчанию - 5672
            $this->user,        #user - имя пользователя для соединения с сервером
            $this->password     #password
        );


        /** @var $channel AMQPChannel */
        $channel = $connection->channel();

        $channel->queue_declare(
            $this->queue,    #queue name - Имя очереди может содержать до 255 байт UTF-8 символов
            false,        #passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
            true,        #durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
            false,        #exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
            false        #autodelete - очередь удаляется, когда отписывается последний подписчик
        );

        $msg = new AMQPMessage($id);

        $channel->basic_publish(
            $msg,           #message
            'router',       #exchange
            $this->queue    #routing key
        );

        $channel->close();
        $connection->close();
    }


    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     */

    public function process_deductions($message)
    {
        echo "\n--- Message received -----\n";
        echo "ID: ". $message->body;
        echo "\n--------\n";

        // Начинаем обработку сообщения
        // Находим модель с письмом MSG

        $model = StoreDeduction::find()->andWhere('id = :id',[':id' => $message->body ])->one();

        if(!$model)
                throw new Exception("Can't find deduction");

       //Пересчитываем остаток и проверяем что он положительный
       //есди все ок, то ставим статус списание проведено
        //если нет то аннулируем данное списание
    }
}