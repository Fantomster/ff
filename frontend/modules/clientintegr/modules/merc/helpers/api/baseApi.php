<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api;

use Yii;
use api\common\models\merc\mercDicconst;
use yii\base\Component;

class baseApi extends Component
{
    protected $login;
    protected $pass;
    protected $apiKey;
    protected $issuerID;
    protected $service_id = 'mercury-g2b.service';
    protected $vetisLogin = '';
    protected $_client;
    protected $enterpriseGuid;
    protected $wsdls;

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance[static::class] === null) {
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
            return new \SoapClient($this->wsdls[$system]['wsdl'],[
                'login' => $this->login,
                'password' => $this->pass,
                'exceptions' => 1,
                'trace' => 1,
            ]);

        return $this->_client;
    }

    protected function getLocalTransactionId($method)
    {
        return base64_encode($method.time());
    }
}
