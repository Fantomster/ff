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
   const GET_USERDATA = 1;
   const GET_UPDATES_DICTS = 2;

    protected $login;
    protected $pass;
    protected $apiKey;
    protected $issuerID;
    protected $service_id = '';
    protected $vetisLogin = '';
    protected $_client;
    protected $enterpriseGuid;
    protected $wsdls;
    protected $query_timeout;
    protected $mode = self::GET_USERDATA;

    protected static $_instance = [];

    public static function getInstance($org_id = null)
    {
        $key = isset($org_id) ? static::class."_".$org_id : static::class;
        if (!array_key_exists($key, self::$_instance)) {
            self::$_instance[$key] = new static();
            self::$_instance[$key]->wsdls = Yii::$app->params['merc_settings'];
            self::$_instance[$key]->login = mercDicconst::getSetting('auth_login', $org_id);
            self::$_instance[$key]->pass = mercDicconst::getSetting('auth_password', $org_id);
            self::$_instance[$key]->apiKey = mercDicconst::getSetting('api_key', $org_id);
            self::$_instance[$key]->issuerID = mercDicconst::getSetting('issuer_id', $org_id);
            self::$_instance[$key]->vetisLogin = mercDicconst::getSetting('vetis_login', $org_id);
            self::$_instance[$key]->enterpriseGuid = mercDicconst::getSetting('enterprise_guid', $org_id);
            self::$_instance[$key]->query_timeout = Yii::$app->params['merc_settings']['query_timeout'];
            self::$_instance[$key]->service_id = Yii::$app->params['merc_settings']['mercury']['service_id'];
        }
        return self::$_instance[$key];
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
