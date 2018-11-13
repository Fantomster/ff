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
    public $initiator;
    public $conditions;
    //step-1
    public $step1;
    //step-2
    public $step2;
    /*public $purpose;
    public $cargoExpertized;
    public $locationProsperity;*/
    //step-3
    public $step3;
    /*public $recipient;
    public $hc;
    public $isTTN;
    public $seriesTTN;
    public $numberTTN;
    public $dateTTN;
    public $typeTTN;
    public $hc_name;*/
    //step-4
    public $step4;
    /*public $type;
    public $type_name;
    public $car_number;
    public $trailer_number;
    public $container_number;
    public $storage_type;*/




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

        if(isset($this->conditions)) {
            $this->conditions = json_decode($this->conditions, true);
        }

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
            $authentication['purpose']['guid'] = $this->step2['purpose'];
            $authentication['cargoExpertized'] = $this->step2['cargoExpertized'];
            $authentication['locationProsperity'] = $this->step2['locationProsperity'];

            //Заполняем условия регионализации при необходимости
           //var_dump($this->conditions); die();
            if(isset($this->conditions[$product['product_name']])) {
                $conditions = null;
                $buff = $this->conditions[$product['product_name']];
                foreach ($buff as $key=>$item) {
                    $r13nClause = new RegionalizationClause();
                    $r13nClause->condition = new RegionalizationCondition();
                    $r13nClause->condition->guid = $key;
                    $conditions[] = $r13nClause;
                }
                $authentication['r13nClause'] = $conditions;
            }
            $vetCertificate->authentication = $authentication;
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

       /* echo "<pre>";
        var_dump($request); die();*/
        return $request;
    }

    public function checkShipmentRegionalizationOperation ()
    {
        foreach ($this->step1 as $id => $product) {
            $stock = MercStockEntry::findOne(['id' => $id]);
            $stock_raw = json_decode(json_encode(unserialize($stock->raw_data)), true);
            $result = mercuryApi::getInstance()->checkShipmentRegionalizationOperation($this->step3['recipient'], mercDicconst::getSetting('enterprise_guid'), $stock_raw["batch"]["subProduct"]['guid']);
            if ($result == null) {
                throw new \Exception('CheckShipmentRegionalizationOperation error');
            }

            $result = is_array($result) ? $result : [$result];
            $this->conditions = null;
            foreach ($result as $item) {
                $item = json_decode(json_encode($item), true);
                switch ($item['appliedR13nRule']['decision']) {
                    case 1 :
                        break;                         //Можно делать перемещение без ограничений
                    case 2 ://Можно делать перемещение при соблюдении условий
                        foreach ($item['appliedR13nRule']['requirement'] as $requirement) {
                            $conditionGroup = is_array($requirement["conditionGroup"]) ? $requirement["conditionGroup"] : [$requirement["conditionGroup"]];
                            foreach ($conditionGroup as $group) {
                                $group = !array_key_exists('condition', $group) ? $group : $group['condition'];
                                $condition = !array_key_exists('guid', $group) ? $group : [$group];
                                foreach ($condition as $cond) {
                                    if ($cond['active'] && $cond['last']) {
                                        $conditions[$product][$cond['guid']] = $cond['text'];
                                    }
                                }
                            }
                        }
                        break;
                    case 3 :
                        throw new Exception('Пересещение запрещено правилами регионализации (' . $item['appliedR13nRule']['requirement']['relatedDisease']['name'] . ')!');
                }
            }
        }

        $this->conditions = (isset($this->conditions)) ?  json_encode($this->conditions) : null;

    }
}