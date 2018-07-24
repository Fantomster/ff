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

class CreateRegisterProductionRequest extends Component{

    public $localTransactionId;
    public $initiator;
    public $step1;
    public $step2;


    public function getRegisterProductionRequest()
    {
        $request = new PrepareOutgoingConsignmentRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->initiator;
        $enterprise = mercDicconst::getSetting('enterprise_guid');
        $hc =  mercDicconst::getSetting('issuer_id');

        //dd($this->step2);

        $delivery = new Delivery();
        $delivery->consignor = new BusinessMember();
        $delivery->consignor->enterprise = (cerberApi::getInstance()->getEnterpriseByGuid($enterprise))->enterprise;
        $delivery->consignor->businessEntity = (cerberApi::getInstance()->getBusinessEntityByGuid($hc))->businessEntity;

        $delivery->consignee = new BusinessMember();

        $consigments = [];
        $vetCertificates = [];
        foreach ($this->step1 as $id => $product) {
            $consigment = new Consignment();
            $consigment->id = 'con'.$id;
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

            $consigments[] = $consigment;

            $vetCertificate = new VetDocument();
            $vetCertificate->for = 'con'.$id;
            $vetCertificate->authentication = new VeterinaryAuthentication();
            $vetCertificate->authentication->purpose = new Purpose();
            $vetCertificate->authentication->purpose->guid = $this->step2['purpose'];
            $vetCertificate->authentication->cargoExpertized = $this->step2['cargoExpertized'];
            $vetCertificate->authentication->locationProsperity = $this->step2['locationProsperity'];

            $vetCertificates[] = $vetCertificate;
        }

        $delivery->consignment = $consigments;

        $delivery->transportInfo = new TransportInfo();
        //dd($delivery);


        $delivery->accompanyingForms = new ConsignmentDocumentList();
        if($this->step3['isTTN']) {
            $delivery->accompanyingForms->waybill = new Waybill();
            $delivery->accompanyingForms->waybill->issueSeries = $this->step3['seriesTTN'];
            $delivery->accompanyingForms->waybill->issueNumber = $this->step3['numberTTN'];
            $delivery->accompanyingForms->waybill->issueDate = date('Y-m-d', $this->step3['dateTTN']);
            $delivery->accompanyingForms->waybill->type = $this->typeTTN;
        }

        $delivery->accompanyingForms->vetCertificate = $vetCertificates;

        $request->delivery = $delivery;

        return $request;
    }

}