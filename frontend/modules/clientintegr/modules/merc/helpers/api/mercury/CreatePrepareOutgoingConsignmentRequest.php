<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 13:29
 */

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;


use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use yii\base\Component;

class CreatePrepareOutgoingConsignmentRequest extends Component{

    public $localTransactionId;
    public $iniciator;
    //step-1
    public $products;
    //step-2
    public $purpose;
    public $cargoExpertized;
    public $locationProsperity;
    //step-3
    public $recipient;
    public $hc;
    public $isTTN;
    public $seriesTTN;
    public $numberTTN;
    public $dateTTN;
    public $typeTTN;
    public $hc_name;
    //step-4
    public $type;
    public $type_name;
    public $car_number;
    public $trailer_number;
    public $container_number;
    public $storage_type;




    public function getPrepareOutgoingConsignmentRequest()
    {
        $request = new PrepareOutgoingConsignmentRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->iniciator;

        $enterprise = mercDicconst::getSetting('enterprise_guid');
        $hc =  mercDicconst::getSetting('issuer_id');

        $enterprise = cerberApi::getInstance()->getEnterpriseByGuid($enterprise);
        $hc = cerberApi::getInstance()->getBusinessEntityByGuid($hc);

        $delivery = new Delivery();
        $delivery->consignor = new BusinessMember();
        $delivery->consignor->enterprise = $enterprise;
        $delivery->consignor->businessEntity = $hc;

        $enterprise = cerberApi::getInstance()->getEnterpriseByGuid($this->recipient);
        $hc = cerberApi::getInstance()->getBusinessEntityByGuid($this->hc);

        $delivery->consignee = new BusinessMember();
        $delivery->consignee->enterprise = $enterprise;
        $delivery->consignee->businessEntity = $hc;

        //$consigment = [];
        foreach ($this->products as $id => $product) {
            $consigment = new Consignment();
            $consigment[]->id = $id;
            $stock = MercStockEntry::findOne(['id' => $id]);
            $stock_raw = unserialize($stock->raw_data);
            if($stock->product_name != $product['product_name'])
            {

            }
            $consigment->volume = $product['select_amount'];
            $consigment->unit = new Unit();
            $consigment->unit = $stock_raw->batch->unit;

            $consigment->sourceStockEntry = new StockEntry();
            $consigment->sourceStockEntry->uuid = $stock->uuid;
            $consigment->sourceStockEntry->guid = $stock->guid;

            $vetCertificate = new VetDocument();
            $vetCertificate->for = $id;
            $vetCertificate->authentication = new VeterinaryAuthentication();
            $vetCertificate->authentication->purpose = new Purpose();
            $vetCertificate->authentication->purpose->uuid = $this->purpose;
            $vetCertificate->authentication->cargoExpertized = $this->cargoExpertized;
            $vetCertificate->authentication->locationProsperity = $this->locationProsperity;
        }

        $delivery->consignment = $consigment;

        $delivery->transportInfo = new TransportInfo();
        $delivery->transportInfo->transportType = $this->type;
        $delivery->transportInfo->transportNumber = new TransportNumber();
        $delivery->transportInfo->transportNumber->vehicleNumber = $this->car_number;
        $delivery->transportInfo->transportNumber->trailerNumber = $this->trailer_number;
        $delivery->transportInfo->transportNumber->containerNumber = $this->container_number;

        $delivery->transportStorageType = $this->storage_type;

        $delivery->accompanyingForms = new ConsignmentDocumentList();
        if($this->isTTN) {
            $delivery->accompanyingForms->waybill = new Waybill();
            $delivery->accompanyingForms->waybill->issueSeries = $this->seriesTTN;
            $delivery->accompanyingForms->waybill->issueNumber = $this->numberTTN;
            $delivery->accompanyingForms->waybill->issueDate = date('Y-m-d', $this->dateTTN);
            $delivery->accompanyingForms->waybill->type = $this->typeTTN;
        }



    }

}