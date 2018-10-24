<?php

namespace api_web\modules\integration\modules\vetis\api;

use api_web\components\Registry;
use common\models\IntegrationSettingValue;
use yii\base\Component;

/**
 * Class baseApi
 *
 * @package api_web\modules\integration\modules\vetis\api
 */
class baseApi extends Component
{
    /**
     *
     */
    const GET_USERDATA = 1;
    /**
     *
     */
    const GET_UPDATES_DICTS = 2;

    /**
     * @var
     */
    protected $login;
    /**
     * @var
     */
    protected $pass;
    /**
     * @var
     */
    protected $apiKey;
    /**
     * @var
     */
    protected $issuerID;
    /**
     * @var string
     */
    protected $service_id = '';
    /**
     * @var string
     */
    protected $vetisLogin = '';
    /**
     * @var
     */
    protected $_client;
    /**
     * @var
     */
    protected $enterpriseGuid;
    /**
     * @var
     */
    protected $wsdls;
    /**
     * @var
     */
    protected $query_timeout;
    /**
     * @var
     */
    protected $system;
    /**
     * @var
     */
    protected $wsdlClassName;
    /**
     * @var
     */
    protected $org_id;

    /**
     * @var array
     */
    protected static $_instance = [];

    /**
     * @param null $org_id
     * @return mixed
     */
    public static function getInstance($org_id = null)
    {
        $key = isset($org_id) ? static::class . "_" . $org_id : static::class;
        if (!array_key_exists($key, self::$_instance)) {
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::MERC_SERVICE_ID, $org_id);
            self::$_instance[$key] = new static();
            self::$_instance[$key]->wsdls = \Yii::$app->params['merc_settings'];
            self::$_instance[$key]->login = $settings['auth_login'];
            self::$_instance[$key]->pass = $settings['auth_password'];
            self::$_instance[$key]->apiKey = $settings['api_key'];
            self::$_instance[$key]->issuerID = $settings['issuer_id'];
            self::$_instance[$key]->vetisLogin = $settings['vetis_login'];
            self::$_instance[$key]->enterpriseGuid = $settings['enterprise_guid'];
            self::$_instance[$key]->query_timeout = \Yii::$app->params['merc_settings']['query_timeout'];
            self::$_instance[$key]->service_id = \Yii::$app->params['merc_settings']['mercury']['service_id'];
            self::$_instance[$key]->org_id = isset($org_id) ? $org_id : (\Yii::$app->user->identity)->organization_id;
        }
        return self::$_instance[$key];
    }

    /**
     * @return mixed
     */
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

    /**
     * @param $method
     * @return string
     */
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
