<?php
/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:07
 */

namespace common\components\sms;


abstract class AbstractProvider
{
    /**
     * Отправка СМС возвращает id отправленной смс от API
     * @param $message
     * @param $target
     * @return mixed
     */
    public abstract function send($message, $target);

    /**
     * Установка свойст провайдера
     * @param $name
     * @param $value
     */
    public function setProperty($name, $value) {
        $this->$name = $value;
    }
}