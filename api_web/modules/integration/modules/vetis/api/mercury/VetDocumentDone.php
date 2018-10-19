<?php

namespace api_web\modules\integration\modules\vetis\api\mercury;

use yii\base\Component;

/***
 * This is the model class for table "iiko_dic".
 *
 * @property VetDocument $id
 * @property string      $login
 * @property string      $UUID
 * @property string      $type
 * @property array       $rejected_data
 * @property string      $localTransactionId
 */
class VetDocumentDone extends Component
{

    /**
     * Погашение ВСД
     */
    const ACCEPT_ALL = 'ACCEPT_ALL';
    /**
     * Частичная приемка
     */
    const PARTIALLY = 'PARTIALLY';
    /**
     * Возврат
     */
    const RETURN_ALL = 'RETURN_ALL';

    /**
     * @var
     */
    private $doc; //VetDocument
    /**
     * @var
     */
    private $login;
    /**
     * @var
     */
    private $UUID;
    /**
     * @var
     */
    private $type;
    /**
     * @var
     */
    private $rejected_data;
    /**
     * @var
     */
    private $localTransactionId;

    /**
     * @param array $config
     */
    public function init(array $config = [])
    {
        if (!empty($config)) {
            $this->login = $config['login'];
            $this->UUID = $config['UUID'];
            $this->type = $config['type'];
            $this->rejected_data = $config['rejected_data'];
            $this->localTransactionId = $config['localTransactionId'];
        }
        parent::init();
    }

    /**
     * @return ProcessIncomingConsignmentRequest
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getProcessIncomingConsignmentRequest()
    {
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury();
        $this->doc = (new getVetDocumentByUUID())->getDocumentByUUID($this->UUID, true);

        $data = new ProcessIncomingConsignmentRequest();
        $date = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . \Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $data->localTransactionId = $this->localTransactionId;
        $data->initiator = new User();
        $data->initiator->login = $this->login;

        $data->delivery = new Delivery();
        $data->delivery->deliveryDate = $date;

        $consignor = $this->doc->certifiedConsignment->consignor;
        $consignor->businessEntity->uuid = null;
        $consignor->enterprise->uuid = null;
        $data->delivery->consignor = $consignor;

        $consignee = $this->doc->certifiedConsignment->consignee;
        $consignee->businessEntity->uuid = null;
        $consignee->enterprise->uuid = null;
        $data->delivery->consignee = $consignee;

        $consigment = new Consignment();
        $consigment->productType = $this->doc->certifiedConsignment->batch->productType;
        $consigment->product = $this->doc->certifiedConsignment->batch->product;
        $consigment->subProduct = $this->doc->certifiedConsignment->batch->subProduct;
        $consigment->productItem = $this->doc->certifiedConsignment->batch->productItem;

        $volume = $this->doc->certifiedConsignment->batch->volume;
        $consigment->volume = (($this->type == self::RETURN_ALL) ? 0 : (isset($this->rejected_data['volume']) ? $this->mb_abs($this->rejected_data['volume']) : $volume));

        $consigment->unit = $this->doc->certifiedConsignment->batch->unit;

        if (isset($doc->certifiedConsignment->batch->packingList)) {
            $consigment->packageList = $this->doc->certifiedConsignment->batch->packingList;
        }
        $consigment->dateOfProduction = $this->doc->certifiedConsignment->batch->dateOfProduction;

        if (isset($this->doc->certifiedConsignment->batch->expiryDate)) {
            $consigment->expiryDate = $this->doc->certifiedConsignment->batch->expiryDate;
        }
        $consigment->batchID = $this->doc->certifiedConsignment->batch->batchID;
        $consigment->perishable = $this->doc->certifiedConsignment->batch->perishable;

        $origin = new BatchOrigin();
        $origin->country = $this->doc->certifiedConsignment->batch->origin->country;
        $origin->producer = $this->doc->certifiedConsignment->batch->origin->producer;

        $consigment->origin = $origin;
        $consigment->lowGradeCargo = $this->doc->certifiedConsignment->batch->lowGradeCargo;

        $data->delivery->consignment = $consigment;

        if (isset($doc->certifiedConsignment->broker)) {
            $data->delivery->broker = $this->doc->certifiedConsignment->broker;
        }
        $data->delivery->transportInfo = $this->doc->certifiedConsignment->transportInfo;
        $data->delivery->transportStorageType = $this->doc->certifiedConsignment->transportStorageType;

        $accompanyingForms = new ConsignmentDocumentList();
        if (isset($this->doc->referencedDocument)) {
            $docs = null;
            if (!is_array($this->doc->referencedDocument)) {
                $docs[] = $this->doc->referencedDocument;
            } else {
                $docs = $this->doc->referencedDocument;
            }
            foreach ($docs as $item) {
                if (($item->type >= 1) && ($item->type <= 5)) {
                    $accompanyingForms->waybill = new Waybill();
                    $accompanyingForms->waybill->issueSeries = isset($item->issueSeries) ? $item->issueSeries : null;
                    $accompanyingForms->waybill->issueNumber = $item->issueNumber;
                    $accompanyingForms->waybill->issueDate = $item->issueDate;
                    $accompanyingForms->waybill->type = $item->type;
                    break;
                }
            }
        }
        $accompanyingForms->vetCertificate = new VetDocument();
        $accompanyingForms->vetCertificate->uuid = $this->UUID;
        $data->delivery->accompanyingForms = $accompanyingForms;

        $facts = new DeliveryFactList();
        $facts->vetCertificatePresence = 'ELECTRONIC';
        $facts->decision = $this->type;
        $facts->docInspection = new DeliveryInspection();
        $facts->docInspection->responsible = new User();
        $facts->docInspection->responsible->login = $this->login;
        $facts->docInspection->result = 'CORRESPONDS';
        $facts->vetInspection = new DeliveryInspection();
        $facts->vetInspection->responsible = new User();
        $facts->vetInspection->responsible->login = $this->login;
        $facts->vetInspection->result = 'CORRESPONDS';

        $data->deliveryFacts = $facts;

        if ($this->type != self::ACCEPT_ALL) {
            $data->discrepancyReport = $this->getDiscrepancyReport();
            \Yii::$app->cache->add('byf_doc', $this->doc);
            $data->returnedDelivery = $this->returnedDelivery($date);
        }
        return $data;
    }

    /**
     * @return DiscrepancyReport
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getDiscrepancyReport()
    {
        $report = new DiscrepancyReport();
        $report->issueDate = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd');
        $report->reason = new DiscrepancyReason();
        $report->reason->name = $this->rejected_data['reason'];
        $report->description = $this->rejected_data['reason'];

        return $report;
    }

    /**
     * @param $date
     * @return Delivery
     */
    public function returnedDelivery($date)
    {
        $doc = \Yii::$app->cache->get('byf_doc');
        \Yii::$app->cache->delete('byf_doc');

        $retuned = new Delivery();
        $retuned->deliveryDate = $date;

        $consignor = $doc->certifiedConsignment->consignee;
        $consignor->businessEntity->uuid = null;
        $consignor->enterprise->uuid = null;

        $retuned->consignor = $consignor;

        $consignee = $doc->certifiedConsignment->consignor;
        $consignee->businessEntity->uuid = null;
        $consignee->enterprise->uuid = null;

        $retuned->consignee = $consignee;

        $consigment = new Consignment();
        $consigment->productType = $doc->certifiedConsignment->batch->productType;

        $consigment->product = $doc->certifiedConsignment->batch->product;

        $consigment->subProduct = $doc->certifiedConsignment->batch->subProduct;
        $consigment->productItem = $doc->certifiedConsignment->batch->productItem;

        $volume = $doc->certifiedConsignment->batch->volume;

        $consigment->volume = (($this->type == self::RETURN_ALL) ? $volume : $this->mb_abs($volume - $this->rejected_data['volume']));

        $consigment->unit = $doc->certifiedConsignment->batch->unit;

        if (isset($doc->certifiedConsignment->batch->packingList)) {
            $consigment->packageList = $doc->certifiedConsignment->batch->packingList;
        }
        $consigment->dateOfProduction = $doc->certifiedConsignment->batch->dateOfProduction;

        if (isset($doc->certifiedConsignment->batch->expiryDate)) {
            $consigment->expiryDate = $doc->certifiedConsignment->batch->expiryDate;
        }
        $consigment->batchID = $doc->certifiedConsignment->batch->batchID;
        $consigment->perishable = $doc->certifiedConsignment->batch->perishable;
        $consigment->origin = $doc->certifiedConsignment->batch->origin;
        $consigment->lowGradeCargo = $doc->certifiedConsignment->batch->lowGradeCargo;

        $retuned->consignment = $consigment;

        if (isset($doc->certifiedConsignment->broker)) {
            $retuned->broker = $doc->certifiedConsignment->broker;
        }
        $retuned->transportInfo = $doc->certifiedConsignment->transportInfo;
        $retuned->transportStorageType = $doc->certifiedConsignment->transportStorageType;

        $accompanyingForms = new ConsignmentDocumentList();
        if (isset($doc->referencedDocument)) {
            $docs = null;
            if (!is_array($doc->referencedDocument)) {
                $docs[] = $doc->referencedDocument;
            } else {
                $docs = $doc->referencedDocument;
            }
            foreach ($docs as $item) {
                if (($item->type >= 1) && ($item->type <= 5)) {
                    $accompanyingForms->waybill = new Waybill();
                    $accompanyingForms->waybill->issueSeries = isset($item->issueSeries) ? $item->issueSeries : null;
                    $accompanyingForms->waybill->issueNumber = $item->issueNumber;
                    $accompanyingForms->waybill->issueDate = $item->issueDate;
                    $accompanyingForms->waybill->type = $item->type;
                    break;
                }
            }
        }

        $accompanyingForms->vetCertificate = new VetDocument();
        $accompanyingForms->vetCertificate->uuid = $this->UUID;
        $accompanyingForms->vetCertificate->authentication = $doc->authentication;
        $retuned->accompanyingForms = $accompanyingForms;

        $retuned->accompanyingForms = $accompanyingForms;

        return $retuned;
    }

    /**
     * @param $number
     * @return mixed
     */
    private function mb_abs($number)
    {
        return str_replace('-', '', $number);
    }
}
