<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api;

use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
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
    protected $system;
    protected $wsdlClassName;
    protected $org_id;

    protected static $_instance = [];

    public static function getInstance($org_id = null)
    {
        return self::getSettingsV1($org_id);
    }

    public static function getSettingsV1($org_id)
    {
        $key = isset($org_id) ? static::class . "_" . $org_id : static::class;
        if (!array_key_exists($key, self::$_instance)) {
            self::$_instance[$key] = new static();
            self::$_instance[$key]->wsdls = Yii::$app->params['merc_settings'];
            self::$_instance[$key]->login = mercDicconst::getSetting('auth_login', $org_id) ?? self::getSettingsV2($org_id, 'auth_login');
            self::$_instance[$key]->pass = mercDicconst::getSetting('auth_password', $org_id) ?? self::getSettingsV2($org_id, 'auth_password');
            self::$_instance[$key]->apiKey = mercDicconst::getSetting('api_key', $org_id) ?? self::getSettingsV2($org_id, 'api_key');
            self::$_instance[$key]->issuerID = mercDicconst::getSetting('issuer_id', $org_id) ?? self::getSettingsV2($org_id, 'issuer_id');
            self::$_instance[$key]->vetisLogin = mercDicconst::getSetting('vetis_login', $org_id) ?? self::getSettingsV2($org_id, 'vetis_login');
            self::$_instance[$key]->enterpriseGuid = mercDicconst::getSetting('enterprise_guid', $org_id) ?? self::getSettingsV2($org_id, 'enterprise_guid');
            self::$_instance[$key]->query_timeout = Yii::$app->params['merc_settings']['query_timeout'];
            self::$_instance[$key]->service_id = Yii::$app->params['merc_settings']['mercury']['service_id'];
            self::$_instance[$key]->org_id = isset($org_id) ? $org_id : (\Yii::$app->user->identity)->organization_id;
        }
        return self::$_instance[$key];
    }

    public static function getSettingsV2($org_id, $denom)
    {
        $setting = VetisHelper::getSettings($org_id, [$denom]);
        return $setting;
    }

    protected function getSoapClient()
    {
        $className = $this->wsdlClassName;
        if ($this->_client === null) {
            $this->_client = (new $className(
                ['url'                => $this->wsdls[$this->system]['wsdl'],
                 'login'              => $this->login,
                 'password'           => $this->pass,
                 'exceptions'         => 1,
                 'connection_timeout' => 500,
                 'cache_wsdl'         => WSDL_CACHE_NONE,
                 'keep_alive'         => false,
                 'trace'              => 1]))->soapClient;
        }

        return $this->_client;
    }

    protected function getLocalTransactionId($method)
    {
        return base64_encode($method . time());
    }

    /**
     * @param string $method
     * @param array  $request
     * @return mixed
     */
    public function sendRequest($method, $request)
    {
        ini_set('default_socket_timeout', 500);
        $client = $this->getSoapClient();
        return $client->$method($request);
    }
}
