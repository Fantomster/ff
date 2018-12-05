<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components\sms\providers;

/**
 * Description of MixedProvider
 *
 * @author El Babuino
 */
class MixedProvider extends Qtelecom
{
    public $sqsQueueUrl;
    
    /**
     * Отправка сообщения
     * @param $message
     * @param $target
     * @return mixed
     */
    public function send($message, $target, $order_id = null)
    {
//        //Если массив из нескольких номеров, строим в строку через запятую
//        if (is_array($target)) {
//            $target = implode(',', $target);
//        }
//        $this->message = trim($message);
//        $this->target = trim($target);
//        //Отправляем сообщение
//        $result = $this->post_message();
//        //Смотрим ответ, записываем ошибки или логи об отправке
//        $this->parseResponse($result);
    }
}
