<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 13:29
 */

namespace api_web\modules\integration\modules\vetis\api\mercury;


use api\common\models\merc\MercStockEntry;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
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
     * @var mixed
     */
    public $params;
    /**
     * @var
     */
    public $conditions;
    /**
     * @var
     */
    public $conditionsDescription;
    /**@var VetisHelper $helper */
    private $helper;

    /**
     * CreatePrepareOutgoingConsignmentRequest constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->helper = new VetisHelper();
    }

    /**
     * @return PrepareOutgoingConsignmentRequest
     * @throws \Exception
     */
    public function getPrepareOutgoingConsignmentRequest()
    {
        $request = new PrepareOutgoingConsignmentRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->initiator;

        $delivery = new Delivery();
        $delivery->consignor = new BusinessMember();
        $delivery->consignor->enterprise = new Enterprise();
        $delivery->consignor->enterprise->guid = $this->helper->getEnterpriseGuid($this->params['org_id']);
        $delivery->consignor->businessEntity = new BusinessEntity();
        $delivery->consignor->businessEntity->guid = $this->helper->getIssuerId($this->params['org_id']);

        $delivery->consignee = new BusinessMember();
        $delivery->consignee->enterprise = new Enterprise();
        $delivery->consignee->enterprise->guid = $this->params['recipient'];
        $delivery->consignee->businessEntity = new BusinessEntity();
        $delivery->consignee->businessEntity->guid = $this->params['hc_guid'];

        $consigments = [];
        $vetCertificates = [];

        if (isset($this->conditions)) {
            $this->conditions = json_decode($this->conditions, true);
        }

        foreach ($this->params['products'] as $product) {
            $consigment = new Consignment();
            $consigment->id = 'con' . $product['id'];
            $stock = MercStockEntry::findOne(['id' => $product['id']]);
            $stock_raw = unserialize($stock->raw_data);
            $consigment->volume = $product['select_amount'];
            $consigment->unit = new Unit();
            $consigment->unit = $stock_raw->batch->unit;

            $consigment->sourceStockEntry = new StockEntry();
            //$consigment->sourceStockEntry->uuid = $stock->uuid;
            $consigment->sourceStockEntry->guid = $stock->guid;

            $consigments[] = $consigment;

            $vetCertificate = new VetDocument();
            $vetCertificate->for = 'con' . $product['id'];
            $authentication['purpose']['guid'] = $this->params['purpose'];
            $authentication['cargoExpertized'] = $this->params['cargoExpertized'];
            $authentication['locationProsperity'] = $this->params['locationProsperity'];

            //Заполняем условия регионализации при необходимости
            //var_dump($this->conditions); die();
            if (isset($this->conditions[$product['product_name']])) {
                $conditions = null;
                $buff = $this->conditions[$product['product_name']];
                foreach ($buff as $key => $item) {
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
        $delivery->transportInfo->transportType = $this->params['type'];
        $delivery->transportInfo->transportNumber = new TransportNumber();
        $delivery->transportInfo->transportNumber->vehicleNumber = $this->params['car_number'];
        if (isset($this->params['trailer_number']) && !empty($this->params['trailer_number'])) {
            $delivery->transportInfo->transportNumber->trailerNumber = $this->params['trailer_number'];
        }
        if (isset($this->params['container_number']) && !empty($this->params['container_number'])) {
            $delivery->transportInfo->transportNumber->containerNumber = $this->params['container_number'];
        }
        $delivery->transportStorageType = $this->params['storage_type'];

        $delivery->accompanyingForms = new ConsignmentDocumentList();
        if ($this->params['isTTN']) {
            $delivery->accompanyingForms->waybill = new Waybill();
            $delivery->accompanyingForms->waybill->issueSeries = $this->params['seriesTTN'];
            $delivery->accompanyingForms->waybill->issueNumber = $this->params['numberTTN'];
            $delivery->accompanyingForms->waybill->issueDate = date('Y-m-d', strtotime($this->params['dateTTN']));
            $delivery->accompanyingForms->waybill->type = $this->params['typeTTN'];
        }

        $delivery->accompanyingForms->vetCertificate = $vetCertificates;

        $request->delivery = $delivery;

        return $request;
    }

    /**
     * @throws \Exception
     */
    public function checkShipmentRegionalizationOperation()
    {
        foreach ($this->params['products'] as $product) {
            $stock = MercStockEntry::findOne(['id' => $product['id']]);
            $stock_raw = json_decode(json_encode(unserialize($stock->raw_data)), true);

            $cond = mercuryApi::getInstance()->getRegionalizationConditions($this->params['recipient'],
                $this->helper->getEnterpriseGuid($this->params['org_id']), $stock_raw["batch"]["subProduct"]['guid']);
            if (isset($cond)) {
                $this->conditionsDescription[$product['product_name']] = $cond;
            }
        }

        $this->conditionsDescription = (isset($this->conditionsDescription)) ? json_encode($this->conditionsDescription) : null;

    }

}
