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
use yii\db\Expression;

class Sms extends \yii\base\Component
{
    public $provider;
    public $attributes;
    private $sender;

    public function init()
    {
        //делаем отправителя из провайдера
        $this->sender = new $this->provider();
        //Проверяем что реализованы все необходимые методы от AbstractProvider
        if ($this->sender instanceof AbstractProvider) {
            //заполняем свойства из конфига
            foreach ($this->attributes as $key => $value) {
                if (property_exists($this->sender, $key)) {
                    $this->sender->setProperty($key, $value);
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
     * @throws \Exception
     */
    public function send($message, $target)
    {
        try {
            //Получаем id смс
            $sms_id = $this->sender->send($message, $target);
            //Сохраняем что отправили смс
            $model = new \common\models\SmsSend([
                'provider' => get_class($this->sender),
                'target' => $target,
                'text' => $message,
                'status' => 1,
                'send_date' => new Expression('NOW()'),
                'sms_id' => $sms_id
            ]);
            //Валидируем, сохраняем
            if ($model->validate()) {
                $model->save();
            }
        } catch (Exception $e) {
            //Сохраняем ошибку в лог, чтобы ошибка при отправке, не рушила систему
            $error = new \common\models\SmsError([
                'message' => $message,
                'target' => $target,
                'error' => $e->getMessage(),
            ]);
            $error->save();
        }
    }


}