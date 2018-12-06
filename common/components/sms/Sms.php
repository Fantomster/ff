<?php

namespace common\components\sms;

use Yii;
use yii\base\Component;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:04
 * Отправка СМС через компонент Yii
 * Yii::$app->sms->send('test','+79162221133');
 */

/**
 * @inheritdoc
 * @property                                         $provider
 * @property array                                   $attributes
 * @property \common\components\sms\AbstractProvider $sender
 */
class Sms extends Component
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
     * instance $provider AbstractProvider
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
                }
            }
            parent::init();
        } else {
            throw new Exception(get_class($this->sender) . ' not instance common\components\sms\AbstractProvider');
        }
    }

    /**
     * Отправка смс
     *
     * @param $message
     * @param $target
     */
    public function send($message, $target, $order_id = null)
    {
        try {
            //Если пустой получатель, игнорируем
            if (empty($target)) {
                throw new Exception(Yii::t('app', 'common.components.sms.empty', ['ru' => 'Поле получатель не может быть пустым. ']));
            }
            //Если пустое сообщение, игнорируем
            if (empty($message)) {
                throw new Exception(Yii::t('app', 'common.components.sms.cant_be', ['ru' => 'Сообщение не может быть пустым. ']));
            }
            //Отправка смс
            $this->sender->send($message, $target, $order_id);
        } catch (Exception $e) {
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * Получить статус СМС сообщения
     *
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

    /**
     * Сборщик текста СМС
     *
     * @param       $source_message
     * @param array $params
     * @param bool  $truncate 70 chars
     * @return string
     */
    public function prepareText($source_message, $params = [], $truncate = true)
    {
        $url = null;
        if (isset($params['url'])) {
            $url = Yii::$app->google->shortUrl(trim($params['url']));
        }
        //Получаем текст смс в текущей локализации
        $text = Yii::t('sms_message', $source_message, $params);
        //Если включена обрезка
        if ($truncate === true) {
            //Если есть урл, отрезаем текст СМС + url
            if ($url !== null) {
                //Если текст смс + url не лезет в 70 символов, отрезаем
                if (mb_strlen($text . ' ' . $url) > 70) {
                    $text = mb_substr($text, 0, 70 - strlen($url) - 4) . '... ' . $url;
                } else {
                    $text .= ' ' . $url;
                }
            } else {
                //Если текст не лезет в 70 символов, отрезаем на 67 и добавляем ...
                if (mb_strlen($text) > 70) {
                    $text = mb_substr($text, 0, 67) . '...';
                }
            }
        } else {
            //Необходимо отправить смс полностью, чтобы то не стоило
            if ($url !== null) {
                $text .= ' ' . $url;
            }
        }
        //Возвращаем подготовленный текст
        return $text;
    }

}
