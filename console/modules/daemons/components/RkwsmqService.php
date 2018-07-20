<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 18.02.2018
 * Time: 21:48
 */
namespace console\modules\daemons\components;

use frontend\modules\clientintegr\modules\rkws\components\RabbitHelper;

class RkwsmqService extends RabbitService
{
    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     */

    public function process_rkws($message)
    {
        echo "\n--- Message received -----\n";
        echo "ID: ";
        var_dump($message->body);
        echo "\n--------\n";

        $result = new RabbitHelper();

        $result->callback(unserialize($message->body));

        // Делаем основную работу
    }
}