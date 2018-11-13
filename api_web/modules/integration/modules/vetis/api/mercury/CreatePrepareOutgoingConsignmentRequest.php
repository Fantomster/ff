<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 13:29
 */

namespace api_web\modules\integration\modules\vetis\api\mercury;


use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;
use yii\base\Component;

/**
 * Class CreatePrepareOutgoingConsignmentRequest
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class CreatePrepareOutgoingConsignmentRequest extends Component{

    /**
     * @var
     */
    public $localTransactionId;
    /**
     * @var
     */
    public $initiator;
    /**
     * @var mixed step-1
     */
    public $step1;
    /**
     * @var mixed step-2
     */
    public $step2;
    /**
     * @var
     */
    public $step3;
    /**
     * @var
     */
    public $step4;

    /**
     * @return PrepareOutgoingConsignmentRequest
     * @throws \Exception
     */
    public function getPrepareOutgoingConsignmentRequest()
    {
        $request = new PrepareOutgoingConsignmentRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->initiator;

        $enterprise = mercDicconst::getSetting('enterprise_guid');
        $hc =  mercDicconst::getSetting('issuer_id');

        $delivery = new Delivery();
        $delivery->consignor = new BusinessMember();
        $delivery->consignor->enterprise = (cerberApi::getInstance()->getEnterpriseByGuid($enterprise));
        $delivery->consignor->businessEntity = (cerberApi::getInstance()->getBusinessEntityByGuid($hc));

        $delivery->consignee = new BusinessMember();
        $delivery->consignee->enterprise = (cerberApi::getInstance()->getEnterpriseByGuid($this->step3['recipient']));
        $delivery->consignee->businessEntity = (cerberApi::getInstance()->getBusinessEntityByGuid($this->step3['hc']));

        $consigments = [];
        $vetCertificates = [];
        foreach ($this->step1 as $id => $product) {
            $consigment = new Consignment();
            $consigment->id = 'con'.$id;
            $stock = MercStockEntry::findOne(['id' => $id]);
            $stock_raw = unserialize($stock->raw_data);
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
        $delivery->transportInfo->transportType = $this->step4['type'];
        $delivery->transportInfo->transportNumber = new TransportNumber();
        $delivery->transportInfo->transportNumber->vehicleNumber = $this->step4['car_number'];
        $delivery->transportInfo->transportNumber->trailerNumber = $this->step4['trailer_number'];
        $delivery->transportInfo->transportNumber->containerNumber = $this->step4['container_number'];

        $delivery->transportStorageType = $this->step4['storage_type'];

        $delivery->accompanyingForms = new ConsignmentDocumentList();
        if($this->step3['isTTN']) {
            $delivery->accompanyingForms->waybill = new Waybill();
            $delivery->accompanyingForms->waybill->issueSeries = $this->step3['seriesTTN'];
            $delivery->accompanyingForms->waybill->issueNumber = $this->step3['numberTTN'];
            $delivery->accompanyingForms->waybill->issueDate = date('Y-m-d', strtotime($this->step3['dateTTN']));
            $delivery->accompanyingForms->waybill->type = $this->step3['typeTTN'];
        }

        $delivery->accompanyingForms->vetCertificate = $vetCertificates;

        $request->delivery = $delivery;

        return $request;
    }

}