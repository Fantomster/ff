<?php
/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:04
 *
 * Отправка СМС через компонент Yii
 * Yii::$app->sms->send('test','+79162221133');
 *
 */

namespace common\components\sms;

use yii\db\Exception;

class Sms extends \yii\base\Component
{
    /**
     *  Класс с реализацией общения с API
     *  Class extends AbstractProvider
     */
    public $provider;

    /**
     * Свойства класса провайдера
     * array
     */
    public $attributes;

    /**
     * instance $provider
     */
    private $sender;

    /**
     * @throws Exception
     */
    public function init()
    {
        if (empty($this->provider)) {
            throw new Exception('provider is empty : main.php -> [components => "sms" => [..."provider" => class, ...]]');
        }
        //делаем отправителя из провайдера
        $this->sender = new $this->provider();
        //Проверяем что реализованы все необходимые методы от AbstractProvider
        if ($this->sender instanceof AbstractProvider) {
            //заполняем свойства из конфига
            foreach ($this->attributes as $key => $value) {
                if (property_exists($this->sender, $key)) {
                    $this->sender->setProperty($key, $value);
                } else {
                    throw new Exception(get_class($this->sender) . ' not property ' . $key);
                }
            }
            parent::init();
        } else {
            throw new Exception(get_class($this->sender) . ' not instance common\components\sms\AbstractProvider');
        }
    }

    /**
     * Отправка смс
     * @param $message
     * @param $target
     */
    public function send($message, $target)
    {
        try {
            //Если пустой получатель, игнорируем
            if (empty($target)) {
                throw new Exception('Поле получатель не может быть пустым. ');
            }
            //Если пустое сообщение, игнорируем
            if (empty($message)) {
                throw new Exception('Сообщение не может быть пустым. ');
            }
            //Отправка смс
            $this->sender->send($message, $target);
        } catch (Exception $e) {
            //Сохраняем ошибку в лог, чтобы ошибка при отправке, не рушила систему
            $this->sender->setError($this->sender->message, $this->sender->target, $e->getMessage());
        }
    }

    /**
     * Получить статус СМС сообщения
     * @param $sms_id
     * @return mixed
     */
    public function checkStatus($sms_id)
    {
        try {
            return $this->sender->checkStatus($sms_id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Получить баланс на аккаунте
     */
    public function getBalance()
    {
        try {
            return $this->sender->getBalance();
        } catch (Exception $e) {
            return null;
        }
    }
}