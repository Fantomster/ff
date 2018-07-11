<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api;

use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\Cerber;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\Dicts;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\Ikar;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\Products;
use Yii;
use api\common\models\merc\mercDicconst;
use yii\base\Component;

class baseApi extends Component
{
    protected $login;
    protected $pass;
    protected $apiKey;
    protected $issuerID;
    protected $service_id = 'mercury-g2b.service:2.0';
    protected $vetisLogin = '';
    protected $_client;
    protected $enterpriseGuid;
    protected $wsdls;

    protected static $_instance = [];

    public static function getInstance()
    {
        if (!array_key_exists(static::class, self::$_instance)) {
            self::$_instance[static::class] = new static();
            self::$_instance[static::class]->wsdls = Yii::$app->params['merc_settings'];
            self::$_instance[static::class]->login = mercDicconst::getSetting('auth_login');
            self::$_instance[static::class]->pass = mercDicconst::getSetting('auth_password');
            self::$_instance[static::class]->apiKey = mercDicconst::getSetting('api_key');
            self::$_instance[static::class]->issuerID = mercDicconst::getSetting('issuer_id');
            self::$_instance[static::class]->vetisLogin = mercDicconst::getSetting('vetis_login');
            self::$_instance[static::class]->enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
            self::$_instance[static::class]->wsdls = Yii::$app->params['merc_settings'];
        }
        return self::$_instance[static::class];
    }

    protected function getSoapClient($system)
    {
        if ($this->_client === null)
            switch ($system) {
            case 'mercury': $this->_client = (new Mercury(
                    ['url' => $this->wsdls[$system]['wsdl'],
                    'login' => $this->login,
                    'password' => $this->pass,
                    'exceptions' => 1,
                    'trace' => 1]))->soapClient;
                break;
            case 'cerber': $this->_client = (new Cerber(
                    ['url' => $this->wsdls[$system]['wsdl'],
                        'login' => $this->login,
                        'password' => $this->pass,
                        'exceptions' => 1,
                        'trace' => 1]))->soapClient;
                break;
            case 'dicts': $this->_client = (new Dicts(
                    ['url' => $this->wsdls[$system]['wsdl'],
                        'login' => $this->login,
                        'password' => $this->pass,
                        'exceptions' => 1,
                        'trace' => 1]))->soapClient;
                break;
            case 'ikar': $this->_client = (new Ikar(
                    ['url' => $this->wsdls[$system]['wsdl'],
                        'login' => $this->login,
                        'password' => $this->pass,
                        'exceptions' => 1,
                        'trace' => 1]))->soapClient;
                break;
            case 'product': $this->_client = (new Products(
                    ['url' => $this->wsdls[$system]['wsdl'],
                        'login' => $this->login,
                        'password' => $this->pass,
                        'exceptions' => 1,
                        'trace' => 1]))->soapClient;
                break;
        }

        return $this->_client;
    }

    protected function getLocalTransactionId($method)
    {
        return base64_encode($method.time());
    }
}
