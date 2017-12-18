<?php

namespace frontend\modules\billing;

use frontend\modules\billing\providers\ProviderInterface;
use yii\helpers\ArrayHelper;
use frontend\modules\billing\handler\BillingErrorHandler;

/**
 * billing module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\billing\controllers';
    private $provider;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        try {
            //Собираем конфиг
            $config = require __DIR__ . '\config.php';
            $local_config = __DIR__ . '\config.local.php';
            if (file_exists($local_config)) {
                $config = ArrayHelper::merge($config, require $local_config);
            }
            //Конфигурируем модуль
            \Yii::configure($this, $config);
            //Регисртируем свой Хэндлер отлова ошибок
            $handler = new BillingErrorHandler;
            \Yii::$app->set('errorHandler', $handler);
            $handler->register();
            //Проверяем что в конфиге
            if (isset($this->params['provider'])) {
                $provider = $this->params['provider'];
                //Создаем провадера
                $this->provider = new $provider();
                //Проверяем что он рабочий от нужного интерфейса
                if ($this->provider instanceof ProviderInterface) {
                    //Смотрим что там не забыли авторизацию
                    if (isset($this->params['auth'])) {
                        //Все прошло хорошо? авторизуемся в провайдере
                        $this->provider = $this->provider->auth($this->params['auth']);
                    } else {
                        throw new \Exception('Empty `auth` in config.php');
                    }
                } else {
                    throw new \Exception(get_class($this->provider) . ' not instance frontend\modules\billing\providers\AbstractProvider');
                }
            } else {
                throw new \Exception('Empty `provider` in config.php');
            }
        } catch (\Exception $e) {
            //Что то пошло не так
            echo $e->getMessage();
            \Yii::$app->end();
        }
    }

    /**
     * @return \frontend\modules\billing\providers\ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
