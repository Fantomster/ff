<?php

namespace api_web\modules\integration\modules\vetis\api\mercury;

use api\common\models\merc\MercVsd;
use console\modules\daemons\classes\MercProductItemList;
use api_web\modules\integration\modules\vetis\api\baseApi;
use api_web\modules\integration\modules\vetis\api\mercLogger;
use frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm;
use frontend\modules\clientintegr\modules\merc\models\productForm;

/**
 * Class mercuryApi
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class mercuryApi extends baseApi
{

    /**
     *
     */
    public function init()
    {
        require_once(__DIR__ . "/Mercury.php");
        $this->system = 'mercury';
        $this->wsdlClassName = Mercury::class;
        parent::init();
    }

    /**
     * @param $enterpriseGuid
     */
    public function setEnterpriseGuid($enterpriseGuid)
    {
        $this->enterpriseGuid = $enterpriseGuid;
    }

    /**
     * @return mixed
     */
    public function getEnterpriseGuid()
    {
        return $this->enterpriseGuid;
    }

    /**
     * @return submitApplicationRequest
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    private function getSubmitApplicationRequest()
    {
        $request = new submitApplicationRequest();
        $request->apiKey = $this->apiKey;

        $request->application = new Application();
        $request->application->serviceId = $this->service_id;
        $request->application->issuerId = $this->issuerID;
        $request->application->issueDate = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . \Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        return $request;
    }

    /**
     * @param      $date_start
     * @param null $listOptions
     * @return null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getVetDocumentChangeList($date_start, $listOptions = null)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $vetDocList = new GetVetDocumentChangesListRequest();
        $vetDocList->localTransactionId = $localTransactionId;
        $vetDocList->enterpriseGuid = $this->enterpriseGuid;
        $vetDocList->initiator = new User();
        $vetDocList->initiator->login = $this->vetisLogin;

        if (isset($listOptions)) {
            $vetDocList->listOptions = $listOptions;
        }

        $vetDocList->updateDateInterval = new DateInterval();
        $vetDocList->updateDateInterval->beginDate = \Yii::$app->formatter->asDate($date_start, 'yyyy-MM-dd') . 'T' . \Yii::$app->formatter->asTime($date_start, 'HH:mm:ss') . '+03:00';
        $vetDocList->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        $appData->any['ns3:getVetDocumentChangesListRequest'] = $vetDocList;

        $request->application->data = $appData;

        try {
            $result = $client->submitApplicationRequest($request);

            $reuest_xml = $client->__getLastRequest();

            $app_id = $result->application->applicationId;
            do {
                //timeout перед запросом результата
                sleep($this->query_timeout);
                //Получаем результат запроса
                $result = $this->getReceiveApplicationResult($app_id);

                $status = $result->application->status;
            } while ($status == 'IN_PROCESS');

            //Пишем лог
            mercLogger::getInstance()->addMercLog($result, 'MercVSDList', $localTransactionId, $reuest_xml, $client->__getLastResponse(), $this->org_id);
        } catch (\SoapFault $e) {
            \Yii::error($e->detail);
        } catch (\Throwable $e) {
            \Yii::error($e);
        }

        return $result;
    }

    /**
     * @param $UUID
     * @return mixed|null
     */
    public function getVetDocumentByUUID($UUID)
    {
        $cache = \Yii::$app->cache;
        $doc = MercVsd::findOne(['uuid' => $UUID]);

        if ($doc != null) {
            return unserialize($doc->raw_data);
        }

        return null;
    }

    /**
     * @param      $UUID
     * @param null $rejectedData
     * @return null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getVetDocumentDone($UUID, $rejectedData = null)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $config['login'] = $this->vetisLogin;
        $config['UUID'] = $UUID;

        if ($rejectedData == null) {
            $config['type'] = VetDocumentDone::ACCEPT_ALL;
        } else {
            $config['type'] = $rejectedData['decision'];
        }

        $config['rejected_data'] = $rejectedData;
        $config['localTransactionId'] = $localTransactionId;

        $vetDoc = new VetDocumentDone();
        $vetDoc->init($config);
        $appData->any['ns3:processIncomingConsignmentRequest'] = $vetDoc->getProcessIncomingConsignmentRequest();

        $request->application->data = $appData;

        try {
            $result = $client->submitApplicationRequest($request);

            $reuest_xml = $client->__getLastRequest();

            $app_id = $result->application->applicationId;
            do {
                //timeout перед запросом результата
                sleep($this->query_timeout);
                //Получаем результат запроса
                $result = $this->getReceiveApplicationResult($app_id);

                $status = $result->application->status;
            } while ($status == 'IN_PROCESS');

            //Пишем лог
            mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

            if ($status == 'COMPLETED') {
                $doc[] = $result->application->result->any['processIncomingConsignmentResponse']->vetDocument;
                (new VetDocumentsChangeList())->updateDocumentsList($doc[0]);

            } else {
                $result = null;
            }
        } catch (\SoapFault $e) {
            \Yii::error($e->detail);
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
        }
        return $result;
    }

    /**
     * @param $applicationId
     * @return mixed
     */
    public function getReceiveApplicationResult($applicationId)
    {
        $client = $this->getSoapClient();
        $request = new receiveApplicationResultRequest();
        $request->apiKey = $this->apiKey;
        $request->issuerId = $this->issuerID;
        $request->applicationId = $applicationId;
        try {
            $result = $client->receiveApplicationResult($request);
        } catch (\SoapFault $e) {
            \Yii::error($e->detail);
        } catch (\Throwable $e) {
            \Yii::error($e);
        }
        return $result;
    }

    /**
     * @param null $listOptions
     * @return mixed|null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function getStockEntryVersionList($listOptions = null)
    {
        $result = null;
        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        $entryList = new GetStockEntryVersionListRequest();
        $entryList->localTransactionId = $localTransactionId;
        $entryList->enterpriseGuid = $this->enterpriseGuid;
        $entryList->initiator = new User();
        $entryList->initiator->login = $this->vetisLogin;

        if (isset($listOptions)) {
            $entryList->listOptions = $listOptions;
        }

        $entryList->searchPattern = new StockEntrySearchPattern();
        $entryList->searchPattern->blankFilter = 'NOT_BLANK';

        $appData->any['ns3:getStockEntryVersionListRequest'] = $entryList;

        $request->application->data = $appData;

        $result = $client->submitApplicationRequest($request);

        $reuest_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep($this->query_timeout);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            //var_dump($result);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        $client = $this->getSoapClient();
        mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

        return $result;
    }

    /**
     * @param      $date_start
     * @param null $listOptions
     * @return mixed|null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function getStockEntryChangesList($date_start, $listOptions = null)
    {
        $result = null;
        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        $entryList = new GetStockEntryChangesListRequest();
        $entryList->localTransactionId = $localTransactionId;
        $entryList->enterpriseGuid = $this->enterpriseGuid;
        $entryList->initiator = new User();
        $entryList->initiator->login = $this->vetisLogin;

        if (isset($listOptions)) {
            $entryList->listOptions = $listOptions;
        }

        $entryList->updateDateInterval = new DateInterval();
        $entryList->updateDateInterval->beginDate = \Yii::$app->formatter->asDate($date_start, 'yyyy-MM-dd') . 'T' . \Yii::$app->formatter->asTime($date_start, 'HH:mm:ss') . '+03:00';
        $entryList->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        $appData->any['ns3:getStockEntryChangesListRequest'] = $entryList;

        $request->application->data = $appData;

        $result = $client->submitApplicationRequest($request);

        $reuest_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep($this->query_timeout);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            //var_dump($result);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        $client = $this->getSoapClient();
        mercLogger::getInstance()->addMercLog($result, 'MercStockEntryList', $localTransactionId, $reuest_xml, $client->__getLastResponse());

        return $result;
    }

    /**
     * @param $GUID
     * @return mixed|null
     */
    public function getStockEntryByGuid($GUID)
    {
        $cache = \Yii::$app->cache;
        $doc = MercVsd::findOne(['guid' => $GUID]);

        if ($doc != null) {
            return unserialize($doc->raw_data);
        }

        return null;
    }

    /**
     * @param $UUID
     * @return mixed|null
     */
    public function getStockEntryByUuid($UUID)
    {
        $cache = \Yii::$app->cache;
        $doc = MercVsd::findOne(['uuid' => $UUID]);

        if ($doc != null) {
            return unserialize($doc->raw_data);
        }

        return null;
    }

    /**
     * @param      $model
     * @param int  $type
     * @param null $data_raws
     * @return mixed|null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function resolveDiscrepancyOperation($model, $type = createStoreEntryForm::ADD_PRODUCT, $data_raws = null)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $report = new ResolveDiscrepancyRequest();
        $report->localTransactionId = $localTransactionId;
        $report->enterprise = new Enterprise();
        $report->enterprise->guid = $this->enterpriseGuid;
        $report->initiator = new User();
        $report->initiator->login = $this->vetisLogin;

        $report->inventoryDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        $report->responsible = new User();
        $report->responsible->login = $this->vetisLogin;

        $count = isset($data_raws) ? count($data_raws) : 1;
        for ($i = 0; $i < $count; $i++) {
            $ID = 'report' . $i;
            $model->raw_stock_entry = isset($data_raws) ? unserialize($data_raws[$i]) : null;
            $model->type = $type;
            $report->stockDiscrepancy[] = $model->getStockDiscrepancy($ID);
            $discrepancyReport = new DiscrepancyReport();
            $discrepancyReport->id = $ID;
            $discrepancyReport->reason = new DiscrepancyReason();
            $discrepancyReport->reason->name = $model->getReason();

            $report->discrepancyReport[] = $discrepancyReport;
        }

        $appData->any['ns3:resolveDiscrepancyRequest'] = $report;

        $request->application->data = $appData;

        //Делаем запрос
        $result = $client->submitApplicationRequest($request);

        $request_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep($this->query_timeout);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $request_xml, $client->__getLastResponse());

        if ($status == 'COMPLETED') {
            $result = $result->application->result->any['resolveDiscrepancyResponse']->stockEntryList->stockEntry;
            (new LoadStockEntryList())->updateDocumentsList($result);
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * @param $data
     * @return mixed|null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function prepareOutgoingConsignmentOperation($data)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        $data->localTransactionId = $localTransactionId;
        $data->initiator = new User();
        $data->initiator->login = $this->vetisLogin;

        $appData->any['ns3:prepareOutgoingConsignmentRequest'] = $data->getPrepareOutgoingConsignmentRequest();

        $request->application->data = $appData;
        //Делаем запрос
        $result = $client->submitApplicationRequest($request);

        $request_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep($this->query_timeout);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $request_xml, $client->__getLastResponse());

        if ($status == 'COMPLETED') {
            $result = $result->application->result->any['prepareOutgoingConsignmentResponse']->stockEntry;
            (new LoadStockEntryList())->updateDocumentsList([1 => $result]);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @param $data
     * @return mixed|null
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function registerProductionOperation($data)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        $data->localTransactionId = $localTransactionId;
        $data->initiator = new User();
        $data->initiator->login = $this->vetisLogin;

        $appData->any['ns3:registerProductionOperationRequest'] = $data->getRegisterProductionRequest();

        $request->application->data = $appData;

        //Делаем запрос
        $result = $client->submitApplicationRequest($request);

        $request_xml = $client->__getLastRequest();

        $app_id = $result->application->applicationId;
        do {
            //timeout перед запросом результата
            sleep($this->query_timeout);
            //Получаем результат запроса
            $result = $this->getReceiveApplicationResult($app_id);

            $status = $result->application->status;
        } while ($status == 'IN_PROCESS');

        //Пишем лог
        mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $request_xml, $client->__getLastResponse());

        if ($status == 'COMPLETED') {
            $stockList = $result->application->result->any['registerProductionOperationResponse']->stockEntryList->stockEntry;
            (new LoadStockEntryList())->updateDocumentsList($stockList);
            $vetDoc[] = $result->application->result->any['registerProductionOperationResponse']->vetDocument;
            (new VetDocumentsChangeList())->updateDocumentsList($vetDoc);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @param $action
     * @param $uuid
     * @param null|productForm $form
     * @return null
     * @throws \Exception
     */
    public function modifyProducerStockListOperation($action, $uuid, $form = null)
    {
        $result = null;

        //Генерируем id запроса
        $localTransactionId = $this->getLocalTransactionId(__FUNCTION__);

        //Готовим запрос
        $client = $this->getSoapClient();

        $request = $this->getSubmitApplicationRequest();

        $appData = new ApplicationDataWrapper();

        //Формируем тело запроса
        $request_body = new ModifyProducerStockListRequest();
        $request_body->localTransactionId = $localTransactionId;
        $request_body->initiator = new User();
        $request_body->initiator->login = $this->vetisLogin;
        $request_body->modificationOperation = new PSLModificationOperation();
        $request_body->modificationOperation->type = $action;

        if ($action == 'DELETE') {
            $affectedList = new ProductItemList();
            $affectedList->productItem = new ProductItem();
            $affectedList->productItem->uuid = $uuid;
            $request_body->modificationOperation->affectedList = $affectedList;
        } else {
            $resultingList = new ProductItemList();
            $productItem = new ProductItem();

            if ($action == 'UPDATE') {
                $productItem->uuid = $uuid;
            }

            $productItem->globalID = !empty($form->globalID) ? $form->globalID : null;
            $productItem->name = $form->name;
            $productItem->code = $form->code;
            $productItem->productType = $form->productType;
            $productItem->product = new Product();
            $productItem->product->guid = $form->product_guid;
            $productItem->subProduct = new SubProduct();
            $productItem->subProduct->guid = $form->subproduct_guid;
            $productItem->correspondsToGost = $form->correspondsToGost;
            $productItem->gost = $form->gost;
            $productItem->producer = new BusinessEntity();
            $productItem->producer->guid = $this->issuerID;
            $productItem->tmOwner = new BusinessEntity();
            $productItem->tmOwner->guid = $this->issuerID;
            $productItem->producing = new ProductItemProducing();
            $productItem->producing->location = new Enterprise();
            $productItem->producing->location->guid = $this->enterpriseGuid;

            $resultingList->productItem = $productItem;
            $request_body->modificationOperation->resultingList = $resultingList;
        }

        $appData->any['ns3:modifyProducerStockListRequest'] = $request_body;

        $request->application->data = $appData;

        try {
            $result = $client->submitApplicationRequest($request);

            $reuest_xml = $client->__getLastRequest();

            $app_id = $result->application->applicationId;
            do {
                //timeout перед запросом результата
                sleep($this->query_timeout);
                //Получаем результат запроса
                $result = $this->getReceiveApplicationResult($app_id);

                $status = $result->application->status;
            } while ($status == 'IN_PROCESS');

            //Пишем лог
            mercLogger::getInstance()->addMercLog($result, __FUNCTION__, $localTransactionId, $reuest_xml, $client->__getLastResponse());

            if ($status == 'COMPLETED') {
                $list = $result->application->result->any['modifyProducerStockListResponse']->productItemList->productItem;
                MercProductItemList::updateList($list);
            } else {
                $result = null;
            }
        } catch (\SoapFault $e) {
            \Yii::error($e->detail);
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
        }
        return $result;
    }

}