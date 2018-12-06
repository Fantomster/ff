<?php

/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:07
 */

namespace common\components\sms;

use yii\db\Exception;

abstract class AbstractProvider
{

    /**
     * Сообщение в кодировке UTF-8
     * @var string
     */
    public $message;

    /**
     * Получатели
     * @var string
     */
    public $target;

    /**
     * Отправка СМС
     * @param $message
     * @param $target
     * @return mixed
     */
    public abstract function send($message, $target);

    /**
     * Проверка статуса СМС от провайдера
     * @param $sms_id
     */
    public abstract function checkStatus($sms_id);

    /**
     * Проверка баланса
     */
    public abstract function getBalance();

    /**
     * Установка свойств провайдера
     * @param $name
     * @param $value
     */
    public function setProperty($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function xmlToArray($data)
    {
        $r   = $xml = simplexml_load_string($data);
        return json_decode(json_encode((array) $r), TRUE);
    }

    /**
     * @param $url
     * @param array $post
     * @param bool $gzip
     * @return mixed|string
     * @throws Exception
     */
    public function curlPost($url, $post = [], $gzip = false)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post, '', '&'));
            $out = curl_exec($curl);
            if ($gzip && !empty($out)) {
                $out = gzinflate(substr($out, 10));
            }
            curl_close($curl);
            return $out;
        } else {
            throw new Exception('Not install CURL extension.');
        }
    }

    /**
     * Сохраняем запись об успешной отправке СМС
     * @param $message
     * @param $target
     * @param $sms_id
     */
    public function sendSmsLog($message, $target, $sms_id)
    {
        //Сохраняем что отправили смс
        $model = new \common\models\SmsSend([
            'provider'  => get_class($this),
            'target'    => $target,
            'text'      => $message,
            'status_id' => 1,
            'sms_id'    => $sms_id
        ]);
        $model->save();
    }

    /**
     * Запись ошибки в базу
     * @param $message
     * @param $target
     * @param $error_message
     */
    public function setError($sms_send_id, $error_code, $error_message)
    {
        //Сохраняем ошибку в лог, чтобы ошибка при отправке, не рушила систему
        $model = new \common\models\SmsError([
            'error'       => $error_message,
            'sms_send_id' => $sms_send_id,
            'error_code'  => $error_code,
        ]);
        $model->save();
    }

}
