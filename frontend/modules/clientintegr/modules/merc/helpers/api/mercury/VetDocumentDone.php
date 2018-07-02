<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use yii\base\Component;

/***
* This is the model class for table "iiko_dic".
 *
 * @property VetDocument $id
* @property string $login
* @property string $UUID
* @property string $type
* @property string $rejected_data
* @property string $localTransactionId
*/

class VetDocumentDone extends Component
{

    const ACCEPT_ALL = 'ACCEPT_ALL';
    const PARTIALLY = 'PARTIALLY';
    const RETURN_ALL = 'RETURN_ALL';

    private $doc; //VetDocument
    private $login;
    private $UUID;
    private $type;
    private $rejected_data;
    private $localTransactionId;

    public function init(array $config = [])
    {
        $this->login = $config['login'];
        $this->UUID = $config['UUID'];
        $this->type = $config['type'];
        $this->rejected_data = $config['rejected_data'];
        $this->localTransactionId = $config['localTransactionId'];
        parent::init(); // TODO: Change the autogenerated stub
    }


    public function getProcessIncomingConsignmentRequest()
    {

        $this->doc = (new \frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest())->getDocumentByUUID($this->UUID, true);

        $data = new ProcessIncomingConsignmentRequest();
        $date = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd').'T'.\Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $data->localTransactionId = $this->localTransactionId;
        $data->initiator = new User();
        $data->initiator->login = $this->login;

        $data->delivery = new Delivery();
        $data->delivery->deliveryDate = $date;

        $consignor = $this->doc->certifiedConsignment->consignor;
        $data->delivery->consignor = $consignor;

        $consignee = $this->doc->certifiedConsignment->consignee;
        $data->delivery->consignee = $consignee;

        $consigment = new Consignment();
        $consigment->productType = $this->doc->certifiedConsignment->batch->productType;
        $consigment->product = $this->doc->certifiedConsignment->batch->product;
        $consigment->subProduct = $this->doc->certifiedConsignment->batch->subProduct;
        $consigment->productItem =  $this->doc->certifiedConsignment->batch->productItem;

        $volume = $this->doc->certifiedConsignment->batch->volume;
        $consigment->volume = (($this->type == self::RETURN_ALL) ? 0 : (isset($this->rejected_data['volume']) ? $this->mb_abs($this->rejected_data['volume']) : $volume));

        $consigment->unit = $this->doc->certifiedConsignment->batch->unit;

        if(isset($doc->certifiedConsignment->batch->packingList))
        $consigment->packageList =  $this->doc->certifiedConsignment->batch->packingList;

        $consigment->dateOfProduction = $this->doc->certifiedConsignment->batch->dateOfProduction;

        if(isset($this->doc->certifiedConsignment->batch->expiryDate))
            $consigment->expiryDate = $this->doc->certifiedConsignment->batch->expiryDate;

        $consigment->batchID = $this->doc->certifiedConsignment->batch->batchID;
        $consigment->perishable = $this->doc->certifiedConsignment->batch->perishable;

        $origin = new BatchOrigin();
        $origin->country = $this->doc->certifiedConsignment->batch->origin->country;
        $origin->producer = $this->doc->certifiedConsignment->batch->origin->producer;

        $consigment->origin = $origin;
        $consigment->lowGradeCargo = $this->doc->certifiedConsignment->batch->lowGradeCargo;

        $data->delivery->consignment = $consigment;

        if(isset($doc->certifiedConsignment->broker))
            $data->delivery->broker = $this->doc->certifiedConsignment->broker;

        $data->delivery->transportInfo = $this->doc->certifiedConsignment->transportInfo;
        $data->delivery->transportStorageType = $this->doc->certifiedConsignment->transportStorageType;

        $accompanyingForms = new ConsignmentDocumentList();
        if(isset($this->doc->delivery->accompanyingForms->waybill))
        $accompanyingForms->waybill = $this->doc->delivery->accompanyingForms->waybill;
        if(isset($this->doc->delivery->accompanyingForms->relatedDocument))
        $accompanyingForms->relatedDocument = $this->doc->delivery->accompanyingForms->relatedDocument;
        $accompanyingForms->vetCertificate = new VetDocument();
        $accompanyingForms->vetCertificate->uuid = $this->UUID;
        $data->delivery->accompanyingForms = $accompanyingForms;
        
        /*$user = new User();
        $user->login = $this->login;*/

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

        if($this->type != self::ACCEPT_ALL) {
            $data->discrepancyReport = $this->getDiscrepancyReport($date);
            $data->returnedDelivery = $this->returnedDelivery($date);
        }
        return $data;
    }

    public function getDiscrepancyReport($date)
    {
        $report = new DiscrepancyReport();
        $report->issueDate = $date;
        $report->reason = new DiscrepancyReason();
        $report->reason->name = $this->rejected_data['reason'];
        $report->description = $this->rejected_data['reason'];

        return $report;
    }

    public function returnedDelivery($date)
    {
        $retuned = new Delivery();
        $retuned->deliveryDate = $date;

        $consignor = $this->doc->certifiedConsignment->consignee;
        $retuned->consignor = $consignor;

        $consignee = $this->doc->certifiedConsignment->consignor;
        $retuned->consignee = $consignee;

        $consigment = new Consignment();
        $consigment->productType = $this->doc->certifiedConsignment->batch->productType;
        $consigment->product = $this->doc->certifiedConsignment->batch->product;
        $consigment->subProduct = $this->doc->certifiedConsignment->batch->subProduct;
        $consigment->productItem =  $this->doc->certifiedConsignment->batch->productItem;

        $volume = $this->doc->certifiedConsignment->batch->volume;

        $consigment->volume = (($this->type == self::RETURN_ALL) ? $volume : $this->mb_abs($volume - $this->rejected_data['volume']));

        $consigment->unit = $this->doc->certifiedConsignment->batch->unit;

        if(isset($doc->certifiedConsignment->batch->packingList))
            $consigment->packageList =  $this->doc->certifiedConsignment->batch->packingList;

        $consigment->dateOfProduction = $this->doc->certifiedConsignment->batch->dateOfProduction;

        if(isset($this->doc->certifiedConsignment->batch->expiryDate))
            $consigment->expiryDate = $this->doc->certifiedConsignment->batch->expiryDate;

        $consigment->batchID = $this->doc->certifiedConsignment->batch->batchID;
        $consigment->perishable = $this->doc->certifiedConsignment->batch->perishable;
        $consigment->origin = $this->doc->certifiedConsignment->batch->origin;
        $consigment->lowGradeCargo = $this->doc->certifiedConsignment->batch->lowGradeCargo;

        $retuned->consignment = $consigment;

        if(isset($doc->certifiedConsignment->broker))
            $retuned->broker = $this->doc->certifiedConsignment->broker;

        $retuned->transportInfo = $this->doc->certifiedConsignment->transportInfo;
        $retuned->transportStorageType = $this->doc->certifiedConsignment->transportStorageType;

        $retuned->accompanyingForms = $this->doc->certifiedConsignment->accompanyingForms;

        return $retuned;
    }

    private function mb_abs($number)
    {
        return str_replace('-','',$number);
    }
}
