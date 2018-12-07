<?php

/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:10
 */

namespace common\components\sms\providers;

use common\components\sms\AbstractProvider;
use common\models\SmsStatus;

class Qtelecom extends AbstractProvider
{

    public $sender;    // Имя отправителя
    public $user;      // ваш логин в системе
    public $pass;      // ваш пароль в системе
    public $period;    // период
    public $post_id;   // пост id
    public $hostname;  // host замените на адрес сервера указанный в меню "Поддержка -> протокол HTTP"  без префикса http://
    public $on_ssl;    // 1 - использовать HTTPS соединение, 0 - HTTP
    public $path;

    /**
     * Отправка сообщения
     * @param $message
     * @param $target
     * @return mixed
     */
    public function send($message, $target)
    {
        //Если массив из нескольких номеров, строим в строку через запятую
        if (is_array($target)) {
            $target = implode(',', $target);
        }
        $this->message = trim($message);
        $this->target  = trim($target);
        //Отправляем сообщение
        $result        = $this->post_message();
        //Смотрим ответ, записываем ошибки или логи об отправке
        $this->parseResponse($result);
    }

    /**
     * Отправка СМС
     * @return mixed|string
     */
    private function post_message()
    {
        //Данные для запроса к АПИ
        $post = [
            'action'               => 'post_sms',
            'message'              => $this->message,
            'sender'               => $this->sender,
            'target'               => $this->target,
            'post_id'              => $this->post_id,
            'period'               => $this->period,
            'user'                 => trim($this->user),
            'pass'                 => trim($this->pass),
            'CLIENTADR'            => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false),
            'HTTP_ACCEPT_LANGUAGE' => (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false)
        ];
        //УРЛ для запроса
        $url  = ($this->on_ssl ? 'https://go.qtelecom.ru' : 'http://' . $this->hostname) . $this->path;
        //Посылаем запрос
        return $this->curlPost($url, $post, true);
    }

    /**
     * Проверка баланса
     * @return mixed
     */
    public function getBalance()
    {
        $post   = [
            'action'               => 'balance',
            'user'                 => trim($this->user),
            'pass'                 => trim($this->pass),
            'CLIENTADR'            => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false),
            'HTTP_ACCEPT_LANGUAGE' => (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false)
        ];
        //УРЛ для запроса
        $url    = ($this->on_ssl ? 'https://go.qtelecom.ru' : 'http://' . $this->hostname) . $this->path;
        //Посылаем запрос
        $result = $this->xmlToArray($this->curlPost($url, $post, true));
        return $result['balance']['AGT_BALANCE'];
    }

    /**
     * Проверка статуса СМС от провайдера
     * @param $sms_id
     * @return mixed
     */
    public function checkStatus($sms_id)
    {
        //Данные для запроса к АПИ
        $post   = [
            'action'               => 'status',
            'sms_id'               => $sms_id,
            'user'                 => trim($this->user),
            'pass'                 => trim($this->pass),
            'CLIENTADR'            => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false),
            'HTTP_ACCEPT_LANGUAGE' => (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false)
        ];
        //УРЛ для запроса
        $url    = ($this->on_ssl ? 'https://go.qtelecom.ru' : 'http://' . $this->hostname) . $this->path;
        //Посылаем запрос
        $result = $this->xmlToArray($this->curlPost($url, $post, true));
        //Разбор результата
        $return = false;
        if (isset($result['MESSAGES'])) {
            switch ($result['MESSAGES']['MESSAGE']['SMSSTC_CODE']) {
                case 'queued':
                case 'wait':
                case 'accepted':
                    $status_id = 1;
                    break;
                case 'delivered':
                    $status_id = 2;
                    break;
                case 'not_delivered':
                    $status_id = 5;
                    break;
                case 'failed':
                    $status_id = 21;
                    break;
            }
            if (isset($status_id)) {
                $return = SmsStatus::findOne(['status' => $status_id]);
            }
        }
        return $return;
    }

    /**
     * Разбор ответа
     * @param $result
     * @throws \yii\db\Exception
     */
    private function parseResponse($result)
    {
        $array = $this->xmlToArray($result);
        //Если никакого ответа
        if (empty($array)) {
            throw new \yii\db\Exception('Пришел пустой результат.');
        }
        //Если пришли ошибки,
        if (isset($array['errors'])) {
            if (is_array($array['errors']['error'])) {
                foreach ($array['errors']['error'] as $error) {
                    $this->setError($array['result']['sms']['@attributes']['id'], $array['errors']['error']['@attributes']['code'], $error);
                }
            } else {
                $this->setError($array['result']['sms']['@attributes']['id'], $array['errors']['error']['@attributes']['code'], $array['errors']['error']);
            }
            return;
        }
        //Если отпавляли нескольким получателям
        if (isset($array['result'])) {
            if (count($array['result']['sms']) > 1) {
                foreach ($array['result']['sms'] as $sms) {
                    $this->sendSmsLog($this->message, $sms['@attributes']['phone'], $sms['@attributes']['id']);
                }
            } elseif (count($array['result']['sms']) == 1) {
                $this->sendSmsLog($this->message, $this->target, $array['result']['sms']['@attributes']['id']);
            }
        }
    }

}
