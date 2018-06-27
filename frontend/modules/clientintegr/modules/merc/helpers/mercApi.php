<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use frontend\modules\clientintegr\modules\merc\helpers\requests\BusinessEntity;
use frontend\modules\clientintegr\modules\merc\models\Cerber;
use frontend\modules\clientintegr\modules\merc\models\getActivityLocationListRequest;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentChangeListRequest;
use Yii;
use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercLog;
use frontend\modules\clientintegr\modules\merc\models\application;
use frontend\modules\clientintegr\modules\merc\models\ApplicationDataWrapper;
use frontend\modules\clientintegr\modules\merc\models\data;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentListRequest;
use frontend\modules\clientintegr\modules\merc\models\receiveApplicationResultRequest;
use frontend\modules\clientintegr\modules\merc\models\submitApplicationRequest;
use yii\base\Component;
use yii\web\BadRequestHttpException;

class mercApi extends Component
{
    private $login;
    private $pass;
    private $apiKey;
    private $issuerID;
    private $service_id = 'mercury-g2b.service';
    private $vetisLogin = '';
    private $_client;
    private $enterpriseGuid;

    private $wsdls;

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
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

    private function getSoapClient($system)
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

    private function getLocalTransactionId($method)
    {
        return base64_encode($method.time());
    }

    private function parseResponse($response, $convert = false)
    {
        if(!$convert) {
            $xmlString = str_replace('SOAP-ENV', 'soapenv', $response);
            $xmlString = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlString);
        }
        else
        $xmlString = $response;

        $xml = simplexml_load_string($xmlString);
        return new \SimpleXMLElement($xml->asXML());
    }

    public function getVetDocumentList($status)
    {
        $client = $this->getSoapClient('mercury');
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
            $vetDoc->status = $status;
            $vetDoc->localTransactionId = $localTransactionId;
            $vetDoc->setEnterpriseGuid($this->enterpriseGuid);
            $vetDoc->setInitiator($this->vetisLogin);
            $application->addData($vetDoc);
            $request->setApplication($application);

            /*var_dump(htmlentities($request->getXML()));
            die();*/

            //Делаем запрос
            $request = $request->getXML();
            $response = $client->__doRequest($request, $this->wsdls['mercury']['Endpoint_URL'], 'submitApplicationRequest', SOAP_1_1);

            /*var_dump(htmlentities($response));
            die();*/

            $result = $this->parseResponse($response);

            if(isset($result->envBody->envFault)) {
                throw new BadRequestHttpException();
            }

            //timeout перед запросом результата
            sleep(2);
            //Получаем результат запроса
            $response = $this->getReceiveApplicationResult($result->envBody->submitApplicationResponse->application->applicationId);

            $result = $this->parseResponse($response);

            //Пишем лог
            $this->addEventLog($result->envBody->receiveApplicationResultResponse, __FUNCTION__, $localTransactionId, $request, $response);


        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    public function getVetDocumentChangeList($date_start)
    {
        $client = $this->getSoapClient('mercury');
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
            $vetDoc = new getVetDocumentChangeListRequest();
            $vetDoc->date_end = time();
            $vetDoc->date_start = $date_start;
            $vetDoc->localTransactionId = $localTransactionId;
            $vetDoc->setEnterpriseGuid($this->enterpriseGuid);
            $vetDoc->setInitiator($this->vetisLogin);
            $application->addData($vetDoc);
            $request->setApplication($application);

            /*var_dump(htmlentities($request->getXML()));
            die();*/

            //Делаем запрос
            $request = $request->getXML();
            $response = $client->__doRequest($request, $this->wsdls['mercury']['Endpoint_URL'], 'submitApplicationRequest', SOAP_1_1);

           /* var_dump(htmlentities($response));
            die();*/

            $result = $this->parseResponse($response);

            if(isset($result->envBody->envFault)) {
                throw new BadRequestHttpException();
            }

            $app_id = $result->envBody->submitApplicationResponse->application->applicationId;
            do {
                //timeout перед запросом результата
                sleep(1);
                //Получаем результат запроса
                $response = $this->getReceiveApplicationResult($app_id);

                $result = $this->parseResponse($response);

                $status = $result->envBody->receiveApplicationResultResponse->application->status->__toString();
            }
            while ($status == 'IN_PROCESS');

            //Пишем лог
            $this->addEventLog($result->envBody->receiveApplicationResultResponse, __FUNCTION__, $localTransactionId, $request, $response);


        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    public function getReceiveApplicationResult ($applicationId)
    {
        $client = $this->getSoapClient('mercury');
        $request = new receiveApplicationResultRequest();
        $request->apiKey = $this->apiKey;
        $request->issuerId = $this->issuerID;
        $request->applicationId = $applicationId;
        return $client->__doRequest($request->getXML(), $this->wsdls['mercury']['Endpoint_URL'], 'receiveApplicationResultRequest', SOAP_1_1);
    }

    private function addEventLog ($response, $method, $localTransactionId, $request, $response_xml)
    {
        //Пишем лог
        $log = new mercLog();
        $log->applicationId = $response->application->applicationId->__toString();
        $log->status = $response->application->status->__toString();
        $log->action = $method;
        $log->localTransactionId =  $localTransactionId;
        $log->request = $request;
        $log->response = $response_xml;

        if($log->status == mercLog::REJECTED) {
            $log->description = json_encode($response->application->errors, JSON_UNESCAPED_UNICODE);
        }

        if (!$log->save())
            var_dump($log->getErrors());
    }

    public function getVetDocumentByUUID($UUID)
    {
        $cache = Yii::$app->cache;
        $doc = $cache->get('vetDocRaw_'.$UUID);

        if (!($doc === false)) {
            //var_dump(2);
            return $this->parseResponse($doc, true);
        }

        $client = $this->getSoapClient('mercury');
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
            $vetDoc = new getVetDocumentByUUIDRequest();
            $vetDoc->localTransactionId = $localTransactionId;
            $vetDoc->setEnterpriseGuid($this->enterpriseGuid);
            $vetDoc->setInitiator($this->vetisLogin);
            $vetDoc->UUID = $UUID;
            $application->addData($vetDoc);
            $request->setApplication($application);

            //Делаем запрос
            $request = $request->getXML($UUID);
            $response = $client->__doRequest($request, $this->wsdls['mercury']['Endpoint_URL'], 'submitApplicationRequest', SOAP_1_1);

            $result = $this->parseResponse($response);

            if(isset($result->envBody->envFault)) {
                throw new BadRequestHttpException();
            }

            //timeout перед запросом результата
            sleep(2);
            //Получаем результат запроса
            $response = $this->getReceiveApplicationResult($result->envBody->submitApplicationResponse->application->applicationId);
            $result = $this->parseResponse($response);

            //Пишем лог
            $this->addEventLog($result->envBody->receiveApplicationResultResponse, __FUNCTION__, $localTransactionId, $request, $response);

            if(isset($result->envBody->envFault)) {
                throw new BadRequestHttpException();
            }

            if($result->envBody->receiveApplicationResultResponse->application->status->__toString() == 'COMPLETED') {
                $result = $result->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentByUuidResponse->ns2vetDocument;
                $cache->add('vetDocRaw_' . $UUID, $result->asXML(), 60 * 5);
            }
            else
                $result = null;

        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    public function getVetDocumentDone($UUID, $rejectedData = null)
    {
        $client = $this->getSoapClient('mercury');
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
            $vetDoc = new vetDocumentDone();
            $vetDoc->login = $this->vetisLogin;
            $vetDoc->UUID = $UUID;
            $vetDoc->rejected_data = $rejectedData;

            if($rejectedData == null)
                $vetDoc->type = vetDocumentDone::ACCEPT_ALL;
            else
                $vetDoc->type = $rejectedData['decision'];

            $vetDoc->doc = (new getVetDocumentByUUIDRequest())->getDocumentByUUID($UUID, true);
            $vetDoc->localTransactionId = $localTransactionId;
            $application->addData($vetDoc);
            $request->setApplication($application);

            /*var_dump(htmlentities($request->getXML()));
            die();*/
            //Делаем запрос
            $request = $request->getXML();

            $response = $client->__doRequest($request, $this->wsdls['mercury']['Endpoint_URL'], 'submitApplicationRequest', SOAP_1_1);

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
            $this->addEventLog($result->envBody->receiveApplicationResultResponse, __FUNCTION__, $localTransactionId, $request, $response);

            if($result->envBody->receiveApplicationResultResponse->application->status->__toString() == 'COMPLETED') {
                $result = true;
            }
            else
                $result = false;


        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }
}
