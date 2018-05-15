<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use api\common\models\merc\mercDic;
use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercPconst;
use frontend\modules\clientintegr\modules\merc\models\application;
use frontend\modules\clientintegr\modules\merc\models\ApplicationDataWrapper;
use frontend\modules\clientintegr\modules\merc\models\data;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentListRequest;
use frontend\modules\clientintegr\modules\merc\models\submitApplicationRequest;
use yii\helpers\ArrayHelper;

class mercApi
{
    private $wsdl = 'http://api.vetrf.ru/schema/platform/services/ApplicationManagementService_v1.4_pilot.wsdl';
    private $login;
    private $pass;
    private $apiKey;
    private $issuerID;
    private $service_id = 'mercury-g2b.service';
    private $vetisLogin = '';

    var $response = '';

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
            self::$_instance->wsdl = \Yii::$app->params['mercury_wsdl'];
            self::$_instance->login = mercDicconst::getSetting('auth_login');
            self::$_instance->pass = mercDicconst::getSetting('auth_password');
            self::$_instance->apiKey = mercDicconst::getSetting('api_key');
            self::$_instance->issuerID = mercDicconst::getSetting('issuer_id');
            self::$_instance->vetisLogin = mercDicconst::getSetting('vetis_login');
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function GetVetDocumentList()
    {
        $client = new \SoapClient($this->wsdl,[
            'login' => $this->login,
            'password' => $this->pass,
            'exceptions' => 1,
            'trace' => 1
        ]);

        try {
            $request = new submitApplicationRequest();
            $request->apiKey = $this->apiKey;
            $application = new application();
            $application->serviceId = $this->service_id;
            $application->issuerId = $this->issuerID;
            $application->issueDate = time();

            $vetDoc = new getVetDocumentListRequest();
            $vetDoc->localTransactionId = 'a10003';

            $application->data = new \SoapVar($vetDoc, XSD_ANYTYPE);
            $request->application = $application;

            $result = $client->submitApplicationRequest($request);
        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }

        echo "Запрос:\n" . htmlentities($client->__getLastRequest()) . "\n";
        echo "Ответ:\n" . htmlentities($client->__getLastResponse()) . "\n";
    }

}
