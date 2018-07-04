<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\mercLog;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;

class mercuryApi extends baseApi
{
    public function init()
    {
        $load = new Mercury();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getVetDocumentList($status)
    {
        $result = null;
        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        $client = $this->getSoapClient('mercury');

        $request = new submitApplicationRequest();
        $request->apiKey = $this->apiKey;

        $request->application = new Application();
        $request->application->serviceId = $this->service_id;
        $request->application->issuerId = $this->issuerID;
        $request->application->issueDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $appData = new ApplicationDataWrapper();

        $vetDocList = new GetVetDocumentListRequest();
        $vetDocList->vetDocumentStatus = $status;
        $vetDocList->localTransactionId = $localTransactionId;
        $vetDocList->enterpriseGuid = $this->enterpriseGuid;
        $vetDocList->initiator = new User();
        $vetDocList->initiator->login = $this->vetisLogin;

        $appData->any['ns3:getVetDocumentListRequest'] = $vetDocList;

        $request->application->data = $appData;


        $result = $client->submitApplicationRequest($request);

        $reuest_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep(1);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            //var_dump($result);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        $client = $this->getSoapClient('mercury');
        $this->addEventLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

        return $result;
    }

    public function getVetDocumentChangeList($date_start)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient('mercury');

        $request = new submitApplicationRequest();
        $request->apiKey = $this->apiKey;

        $request->application = new Application();
        $request->application->serviceId = $this->service_id;
        $request->application->issuerId = $this->issuerID;
        $request->application->issueDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $vetDocList = new GetVetDocumentChangesListRequest();
        $vetDocList->localTransactionId = $localTransactionId;
        $vetDocList->enterpriseGuid = $this->enterpriseGuid;
        $vetDocList->initiator = new User();
        $vetDocList->initiator->login = $this->vetisLogin;

        $vetDocList->updateDateInterval = new DateInterval();
        $vetDocList->updateDateInterval->beginDate = Yii::$app->formatter->asDate($date_start, 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime($date_start, 'HH:mm:ss');
        $vetDocList->updateDateInterval->endDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $appData->any['ns3:getVetDocumentChangesListRequest'] = $vetDocList;

        $request->application->data = $appData;

        $result = $client->submitApplicationRequest($request);

        $reuest_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep(1);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        $this->addEventLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

        return $result;
    }

    public function getVetDocumentByUUID($UUID)
    {
        $cache = Yii::$app->cache;
        $doc = MercVsd::findOne(['uuid' => $UUID]);

        if($doc != null)
            return unserialize($doc->raw_data);

        $result = null;
        $doc = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient('mercury');

        $request = new submitApplicationRequest();
        $request->apiKey = $this->apiKey;

        $request->application = new Application();
        $request->application->serviceId = $this->service_id;
        $request->application->issuerId = $this->issuerID;
        $request->application->issueDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $vetDoc = new GetVetDocumentByUuidRequest();
        $vetDoc->localTransactionId = $localTransactionId;
        $vetDoc->enterpriseGuid = $this->enterpriseGuid;
        $vetDoc->initiator = new User();
        $vetDoc->initiator->login = $this->vetisLogin;
        $vetDoc->uuid = $UUID;


        $appData->any['ns3:getVetDocumentByUuidRequest'] = $vetDoc;

        $request->application->data = $appData;

        //Делаем запрос
        $result = $client->submitApplicationRequest($request);

        $reuest_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep(1);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            $status = $result->application->status;

        } while ($status == 'IN_PROCESS');

        //Пишем лог
        $this->addEventLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

        if ($status == 'COMPLETED') {
            $doc = $result->application->result->any['getVetDocumentByUuidResponse']->vetDocument;
            $cache->add('vetDocRaw_' . $UUID, $doc, 60 * 5);
        } else
            $result = null;

        return $doc;
    }

    public function getVetDocumentDone($UUID, $rejectedData = null)
    {
        $result = null;

        try {
            //Генерируем id запроса
            $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

            //Готовим запрос
            $client = $this->getSoapClient('mercury');

            $request = new submitApplicationRequest();
            $request->apiKey = $this->apiKey;

            $request->application = new Application();
            $request->application->serviceId = $this->service_id;
            $request->application->issuerId = $this->issuerID;
            $request->application->issueDate = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . Yii::$app->formatter->asTime('now', 'HH:mm:ss');

            $appData = new ApplicationDataWrapper();

            //Формируем тело запроса
            $config['login'] = $this->vetisLogin;
            $config['UUID'] = $UUID;

            if ($rejectedData == null)
                $config['type'] = vetDocumentDone::ACCEPT_ALL;
            else
                $config['type'] = $rejectedData['decision'];

            $config['rejected_data'] = $rejectedData;
            $config['localTransactionId'] = $localTransactionId;

            $vetDoc = new VetDocumentDone();
            $vetDoc->init($config);
            $appData->any['ns3:processIncomingConsignmentRequest'] = $vetDoc->getProcessIncomingConsignmentRequest();

            $request->application->data = $appData;

            $result = $client->submitApplicationRequest($request);

            $reuest_xml = $client->__getLastRequest();

            $app_id = $result->application->applicationId;
            do {
                //timeout перед запросом результата
                sleep(1);
                //Получаем результат запроса
                $result = $this->getReceiveApplicationResult($app_id);

                $status = $result->application->status;

            } while ($status == 'IN_PROCESS');

            //Пишем лог
            $this->addEventLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

            if ($status == 'COMPLETED') {
                $doc = $result->application->result->any['getVetDocumentByUuidResponse']->vetDocument;
                Yii::$app->cache->add('vetDocRaw_' . $UUID, $doc, 60 * 5);
            } else
                $result = null;

        } catch (\SoapFault $e) {
            var_dump($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);
        }
        return $result;
    }

    private function addEventLog($response, $method, $localTransactionId, $request_xml, $response_xml)
    {
        //Пишем лог
        $log = new mercLog();
        $log->applicationId = $response->application->applicationId;
        $log->status = $response->application->status;
        $log->action = $method;
        $log->localTransactionId = $localTransactionId;
        $log->request = $request_xml;
        $log->response = $response_xml;

        if ($log->status == mercLog::REJECTED) {
            $log->description = json_encode($response->application->errors, JSON_UNESCAPED_UNICODE);
        }

        if (!$log->save())
            var_dump($log->getErrors());
    }

    public function getReceiveApplicationResult($applicationId)
    {
        $client = $this->getSoapClient('mercury');
        $request = new receiveApplicationResultRequest();
        $request->apiKey = $this->apiKey;
        $request->issuerId = $this->issuerID;
        $request->applicationId = $applicationId;
        return $client->receiveApplicationResult($request);
    }

}