<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
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
    private $wsdl;
    private $enterpriseGuid;

    private $wsdls = [
        'mercury' => [
            'Endpoint_URL' => 'https://api2.vetrf.ru:8002/platform/services/ApplicationManagementService',
            'wsdl' => 'http://api.vetrf.ru/schema/platform/services/ApplicationManagementService_v1.4_pilot.wsdl',
        ],
        'dicts' => [
            'Endpoint_URL' => 'https://api2.vetrf.ru:8002/platform/services/DictionaryService',
            'wsdl' => 'http://api.vetrf.ru/schema/platform/services/DictionaryService_v1.4_pilot.wsdl',
        ],
        'vetis' => [
            'Endpoint_URL' => 'https://api2.vetrf.ru:8002/platform/services/2.0/EnterpriseService',
            'wsdl' => 'http://api.vetrf.ru/schema/platform/cerberus/services/EnterpriseService_v1.4_pilot.wsdl',
        ],
        'product' => [
            'Endpoint_URL' => 'https://api2.vetrf.ru:8002/platform/services/ProductService',
            'wsdl' => 'http://api.vetrf.ru/schema/platform/services/ProductService_v1.4_pilot.wsdl',
        ],
        'ikar' => [
            'Endpoint_URL' => 'https://api2.vetrf.ru:8002/platform/ikar/services/IkarService',
            'wsdl' => 'http://api.vetrf.ru/schema/platform/ikar/services/IkarService_v1.4_pilot.wsdl',
        ],
    ];

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
            self::$_instance->enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        }
        return self::$_instance;
    }

    private function getSoapClient($system)
    {
        if ($this->_client === null)
            return new \SoapClient($this->wsdls[$system]['wsdl'],[
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

    public function getUnitByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $unit = $cache->get('Unit_'.$GUID);

        if(!($unit === false))
            return $this->parseResponse($unit, true);

        $client = $this->getSoapClient('dicts');
        $xml = '<?xml version = "1.0" encoding = "UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://api.vetrf.ru/schema/cdm/argus/common/ws-definitions" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:getUnitByGuidRequest>
         <base:guid>'.$GUID.'</base:guid>
      </ws:getUnitByGuidRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['dicts']['Endpoint_URL'], 'GetUnitByGuid', SOAP_1_1);
        $unit = $this->parseResponse($result);
        if($unit != null)
        $cache->add('Unit_'.$GUID, $unit->asXML(), 60*60*24*7);
        return $unit;
    }

    public function getBusinessEntityByUuid ($UUID)
    {
        $cache = Yii::$app->cache;

        $business = $cache->get('Business_'.$UUID);

        if(!($business === false))
            return $this->parseResponse($business, true);

        $client = $this->getSoapClient('vetis');
        $xml = '<?xml version = "1.0" encoding = "UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
       <soapenv:Header/>
       <soapenv:Body>
          <v2:getBusinessEntityByUuidRequest>
             <base:uuid>'.$UUID.'</base:uuid>
          </v2:getBusinessEntityByUuidRequest>
       </soapenv:Body>
    </soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['vetis']['Endpoint_URL'], 'GetBusinessEntityByUuid', SOAP_1_1);
        $business = $this->parseResponse($result);

        if($business != null && !isset($business->soapBody->soapFault))
        $cache->add('Business_'.$UUID, $business->asXML(), 60*60*24);
        return $business;
    }

    public function getEnterpriseByUuid ($UUID)
    {
        $cache = Yii::$app->cache;
        $enterprise = $cache->get('Enterprise_'.$UUID);

        if(!($enterprise === false))
            return $this->parseResponse($enterprise, true);

        $client = $this->getSoapClient('vetis');
        $xml = '<?xml version = "1.0" encoding = "UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
   <soapenv:Header/>
   <soapenv:Body>
       <v2:getEnterpriseByUuidRequest>
         <base:uuid>'.$UUID.'</base:uuid>
      </v2:getEnterpriseByUuidRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['vetis']['Endpoint_URL'], 'GetEnterpriseByUuid', SOAP_1_1);
        $enterprise = $this->parseResponse($result);

        if($enterprise != null && !isset($enterprise->soapBody->soapFault))
        $cache->add('Enterprise_'.$UUID, $enterprise->asXML(), 60*60*24);
        return $enterprise;
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

    public function getProductByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $product = $cache->get('Product_'.$GUID);

        if(!($product === false))
            return $this->parseResponse($product, true);

        $client = $this->getSoapClient('product');
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://api.vetrf.ru/schema/cdm/argus/production/ws-definitions" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
       <soapenv:Header/>
       <soapenv:Body>
          <ws:getProductByGuidRequest>
             <base:guid>'.$GUID.'</base:guid>
          </ws:getProductByGuidRequest>
       </soapenv:Body>
    </soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['product']['Endpoint_URL'], 'GetProductByGuid', SOAP_1_1);
        //var_dump($result); die();
        $product = $this->parseResponse($result);

        if($product != null)
        $cache->add('Product_'.$GUID, $product->asXML(), 60*60*24);
        return $product;
    }

    public function getSubProductByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $subProduct = $cache->get('SubProduct_'.$GUID);

        if(!($subProduct === false))
            return $this->parseResponse($subProduct, true);

        $client = $this->getSoapClient('product');
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://api.vetrf.ru/schema/cdm/argus/production/ws-definitions" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:getSubProductByGuidRequest>
         <base:guid>'.$GUID.'</base:guid>
      </ws:getSubProductByGuidRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['product']['Endpoint_URL'], 'GetProductByGuid', SOAP_1_1);
        $subProduct = $this->parseResponse($result);

        if($subProduct!= null)
        $cache->add('subProduct_'.$GUID, $subProduct->asXML(), 60*60*24);
        return $subProduct;
    }

    public function getCountryByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $country = $cache->get('Country_'.$GUID);

        if(!($country === false))
            return $this->parseResponse($country, true);

        $client = $this->getSoapClient('ikar');
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://api.vetrf.ru/schema/cdm/ikar/ws-definitions" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:getCountryByGuidRequest>
         <base:guid>'.$GUID.'</base:guid>
      </ws:getCountryByGuidRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['ikar']['Endpoint_URL'], 'GetCountryByGuid', SOAP_1_1);
        $country = $this->parseResponse($result);

        if($country != null)
        $cache->add('Country_'.$GUID, $country->asXML(), 60*60*24*7);
        return $country;
    }

    public function getPurposeByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $purpose = $cache->get('Purpose_'.$GUID);

        if(!($purpose === false))
            return $this->parseResponse($purpose, true);

        $client = $this->getSoapClient('dicts');
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://api.vetrf.ru/schema/cdm/argus/common/ws-definitions" xmlns:base="http://api.vetrf.ru/schema/cdm/base">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:getPurposeByGuidRequest>
         <base:guid>'.$GUID.'</base:guid>
      </ws:getPurposeByGuidRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['dicts']['Endpoint_URL'], 'GetPurposeByGuid', SOAP_1_1);
        $purpose = $this->parseResponse($result);

        if($purpose != null)
        $cache->add('Purpose_'.$GUID, $purpose->asXML(), 60*60*24*7);
        return $purpose;
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


        }catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    public function getActivityLocationList ()
    {
        $client = $this->getSoapClient('vetis');
        $xml = '<?xml version = "1.0" encoding = "UTF-8"?>
            <soapenv:Envelope 
            xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
            xmlns:v2="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2" 
            xmlns:base="http://api.vetrf.ru/schema/cdm/base" 
            xmlns:v21="http://api.vetrf.ru/schema/cdm/dictionary/v2">
   <soapenv:Header/>
   <soapenv:Body>
      <v2:getActivityLocationListRequest>
         <v21:businessEntity>
         		<base:guid>'.$this->issuerID.'</base:guid>
		</v21:businessEntity>
      </v2:getActivityLocationListRequest>
   </soapenv:Body>
</soapenv:Envelope>';
        $result =  $client->__doRequest($xml, $this->wsdls['vetis']['Endpoint_URL'], 'GetActivityLocationList', SOAP_1_1);
        $list = $this->parseResponse($result);

        return $list;
    }
}
