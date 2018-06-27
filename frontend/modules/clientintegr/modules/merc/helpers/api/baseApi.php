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
        if (self::$_instance === null) {
            self::$_instance = new static();
            self::$_instance->wsdls = Yii::$app->params['merc_settings'];
            self::$_instance->login = mercDicconst::getSetting('auth_login');
            self::$_instance->pass = mercDicconst::getSetting('auth_password');
            self::$_instance->apiKey = mercDicconst::getSetting('api_key');
            self::$_instance->issuerID = mercDicconst::getSetting('issuer_id');
            self::$_instance->vetisLogin = mercDicconst::getSetting('vetis_login');
            self::$_instance->enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
            self::$_instance->wsdls = Yii::$app->params['merc_settings'];
        }
        return self::$_instance;
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
