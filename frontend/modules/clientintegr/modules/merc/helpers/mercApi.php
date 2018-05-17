<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use Yii;
use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercLog;
use frontend\modules\clientintegr\modules\merc\models\application;
use frontend\modules\clientintegr\modules\merc\models\ApplicationDataWrapper;
use frontend\modules\clientintegr\modules\merc\models\data;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentListRequest;
use frontend\modules\clientintegr\modules\merc\models\receiveApplicationResultRequest;
use frontend\modules\clientintegr\modules\merc\models\submitApplicationRequest;

class mercApi
{
    private $wsdl = 'http://api.vetrf.ru/schema/platform/services/ApplicationManagementService_v1.4_pilot.wsdl';
    private $login;
    private $pass;
    private $apiKey;
    private $issuerID;
    private $service_id = 'mercury-g2b.service';
    private $vetisLogin = '';
    private $_client;

    const Endpoint_URL = 'https://api2.vetrf.ru:8002/platform/services/ApplicationManagementService';

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

    private function getSoapClient()
    {
        if ($this->_client === null)
            return new \SoapClient($this->wsdl,[
               /* 'use' => SOAP_LITERAL,
                'style' => SOAP_DOCUMENT,
                'location' => self::Endpoint_URL,
                'uri' => 'https://api2.vetrf.ru',*/
                'login' => $this->login,
                'password' => $this->pass,
                'exceptions' => 1,
                'trace' => 1,
                //'soap_version' => SOAP_1_1,
            ]);

        return $$this->_client;
    }

    private function getLocalTransactionId($method)
    {
        return base64_encode($method.time());
    }

    private function parseResponse($response)
    {
        $xmlString = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = simplexml_load_string($xmlString);

        return new \SimpleXMLElement($xml->asXML());
    }

    public function getVetDocumentList()
    {
        $client = $this->getSoapClient();
        $result = null;

        try {
            //Готовим запрос
            $request = new submitApplicationRequest();
            $request->apiKey = $this->apiKey;
            $application = new application();
            $application->serviceId = $this->service_id;
            $application->issuerId = $this->issuerID;
            $application->issueDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd').'T'.Yii::$app->formatter->asTime('now', 'HH:mm:ss');

            //Проставляем id запроса
            $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

            //Формируем тело запроса
            $vetDoc = new getVetDocumentListRequest();
            $vetDoc->localTransactionId = $localTransactionId;
            $vetDoc->setEnterpriseGuid('f8805c8f-1da4-4bda-aaca-a08b5d1cab1b');
            $vetDoc->setInitiator($this->vetisLogin);
            $application->addData($vetDoc);
            $request->setApplication($application);
            
            //Делаем запрос
            $response = $client->__doRequest($request->getXML(), self::Endpoint_URL, 'submitApplicationRequest', SOAP_1_1);

            $result = $this->parseResponse($response);

            if(isset($result->envBody->envFault)) {
                echo "Bad request";
                die();
            }

            //timeout перед запросом результата
            sleep(2);
            //Получаем результат запроса
            $response = $this->getReceiveApplicationResult($result->envBody->submitApplicationResponse->application->applicationId);
            $result = $this->parseResponse($response);

            //Пишем лог
            $this->addEventLog($result->envBody->receiveApplicationResultResponse, __FUNCTION__, $localTransactionId);


        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    public function getReceiveApplicationResult ($applicationId)
    {
        $client = $this->getSoapClient();
        $request = new receiveApplicationResultRequest();
        $request->apiKey = $this->apiKey;
        $request->issuerId = $this->issuerID;
        $request->applicationId = $applicationId;
var_dump(htmlentities($request->getXML()));
        return $client->__doRequest($request->getXML(), self::Endpoint_URL, 'receiveApplicationResultRequest', SOAP_1_1);
        //return $client->receiveApplicationResult($request);
    }

    private function addEventLog ($response, $method, $localTransactionId)
    {
        //Пишем лог
        $log = new mercLog();
        $log->applicationId = $response->application->applicationId->__toString();
        $log->status = $response->application->status->__toString();
        $log->action = $method;
        $log->localTransactionId =  $localTransactionId;
        var_dump("STATUS", $log->status);
        if($log->status == mercLog::REJECTED) {
            var_dump($response);
            $log->description = json_encode($response->application->errors, JSON_UNESCAPED_UNICODE);
        }

        if (!$log->save())
            var_dump($log->getErrors());
    }

}
