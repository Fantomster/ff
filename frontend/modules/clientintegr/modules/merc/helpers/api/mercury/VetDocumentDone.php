<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use yii\base\Component;

/***
 * This is the model class for table "iiko_dic".
 *
 * @property VetDocument $id
 * @property string      $login
 * @property string      $UUID
 * @property string      $type
 * @property string      $rejected_data
 * @property string      $localTransactionId
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
    private $conditions = null;

    public function init(array $config = [])
    {
        if (!empty($config)) {
            $this->login = $config['login'];
            $this->UUID = $config['UUID'];
            $this->type = $config['type'];
            $this->rejected_data = $config['rejected_data'];
            $this->localTransactionId = $config['localTransactionId'];
            if(isset($config['rejected_data']['conditions'])) {
                $this->conditions = $config['rejected_data']['conditions'];
            }
        }
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getProcessIncomingConsignmentRequest()
    {

        $this->doc = (new getVetDocumentByUUID())->getDocumentByUUID($this->UUID, true);

        $date = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T' . \Yii::$app->formatter->asTime('now', 'HH:mm:ss');

        $data['localTransactionId'] = $this->localTransactionId;
        $data['initiator']['login'] = $this->login;

        $data['delivery']['deliveryDate'] = $date;

        $consignor = $this->doc['certifiedConsignment']['consignor'];
        $consignor['businessEntity']['uuid'] = null;
        $consignor['enterprise']['uuid'] = null;
        $data['delivery']['consignor'] = $consignor;

        $consignee = $this->doc['certifiedConsignment']['consignee'];
        $consignee['businessEntity']['uuid'] = null;
        $consignee['enterprise']['uuid'] = null;
        $data['delivery']['consignee'] = $consignee;

        $consigment['productType'] = $this->doc['certifiedConsignment']['batch']['productType'];
        $consigment['product'] = $this->doc['certifiedConsignment']['batch']['product'];
        $consigment['subProduct'] = $this->doc['certifiedConsignment']['batch']['subProduct'];
        $consigment['productItem'] = $this->doc['certifiedConsignment']['batch']['productItem'];

        $volume = $this->doc['certifiedConsignment']['batch']['volume'];
        $consigment['volume'] = (($this->type == self::RETURN_ALL) ? 0 : (isset($this->rejected_data['volume']) ? $this->mb_abs($this->rejected_data['volume']) : $volume));

        $consigment['unit'] = $this->doc['certifiedConsignment']['batch']['unit'];

        if (isset($this->doc['certifiedConsignment']['batch']['packingList']))
            $consigment['packageList'] = $this->doc['certifiedConsignment']['batch']['packingList'];

        $consigment['dateOfProduction'] = $this->doc['certifiedConsignment']['batch']['dateOfProduction'];

        if (isset($this->doc['certifiedConsignment']['batch']['expiryDate']))
            $consigment['expiryDate'] = $this->doc['certifiedConsignment']['batch']['expiryDate'];

        $consigment['batchID'] = $this->doc['certifiedConsignment']['batch']['batchID'];
        $consigment['perishable'] = $this->doc['certifiedConsignment']['batch']['perishable'];

        //$origin = new BatchOrigin();
        $origin['country'] = $this->doc['certifiedConsignment']['batch']['origin']['country'];
        $origin['producer'] = $this->doc['certifiedConsignment']['batch']['origin']['producer'];

        $consigment['origin'] = $origin;
        $consigment['lowGradeCargo'] = $this->doc['certifiedConsignment']['batch']['lowGradeCargo'];

        $data['delivery']['consignment'] = $consigment;

        if (isset($this->doc['certifiedConsignment']['broker']))
            $data['delivery']['broker'] = $this->doc['certifiedConsignment']['broker'];

        $data['delivery']['transportInfo'] = $this->doc['certifiedConsignment']['transportInfo'];
        $data['delivery']['transportStorageType'] = $this->doc['certifiedConsignment']['transportStorageType'];

        if(isset($this->doc['certifiedConsignment']['shipmentRoute'])) {
            $data['delivery']['shipmentRoute'] = $this->doc['certifiedConsignment']['shipmentRoute'];
        }

        $accompanyingForms = [];
        if (isset($this->doc['referencedDocument'])) {
            $docs = null;
            if (isset($this->doc['referencedDocument']['type']))
                $docs[] = $this->doc['referencedDocument'];
            else
                $docs = $this->doc['referencedDocument'];

            foreach ($docs as $item) {
                if (($item['type'] >= 1) && ($item['type'] <= 5)) {
                    $accompanyingForms['waybill']['issueSeries'] = isset($item['issueSeries']) ? $item['issueSeries'] : null;
                    $accompanyingForms['waybill']['issueNumber'] = $item['issueNumber'];
                    $accompanyingForms['waybill']['issueDate'] = $item['issueDate'];
                    $accompanyingForms['waybill']['type'] = $item['type'];
                    break;
                }
            }
        }

        $accompanyingForms['vetCertificate']['uuid'] = $this->UUID;
        $data['delivery']['accompanyingForms'] = $accompanyingForms;

        $facts['vetCertificatePresence'] = 'ELECTRONIC';
        $facts['decision'] = $this->type;
        $facts['docInspection']['responsible']['login'] = $this->login;
        $facts['docInspection']['result'] = 'CORRESPONDS';
        $facts['vetInspection']['responsible']['login'] = $this->login;
        $facts['vetInspection']['result'] = 'CORRESPONDS';

        $data['deliveryFacts'] = $facts;

        if (isset($this->rejected_data)) {
            if ($this->type != self::ACCEPT_ALL) {
                $data['discrepancyReport'] = $this->getDiscrepancyReport($date);
                $data['returnedDelivery'] = $this->returnedDelivery($date, json_decode( json_encode($this->doc), true));
            } else {
                $data['discrepancyReport'] = $this->getDiscrepancyReport($date);
            }
        }
        return $data;
    }

    public function getDiscrepancyReport($date)
    {
        $report['issueDate'] = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd');
        $report['reason']['name'] = $this->rejected_data['reason'];
        $report['description'] = $this->rejected_data['reason'];

        return $report;
    }

    public function returnedDelivery($date, $doc)
    {
        $retuned['deliveryDate'] = $date;

        $consignor = $doc['certifiedConsignment']['consignee'];
        $consignor['businessEntity']['uuid'] = null;
        $consignor['enterprise']['uuid'] = null;

        $retuned['consignor'] = $consignor;

        $consignee = $doc['certifiedConsignment']['consignor'];
        $consignee['businessEntity']['uuid'] = null;
        $consignee['enterprise']['uuid'] = null;

        $retuned['consignee'] = $consignee;

        $consigment['productType'] = $doc['certifiedConsignment']['batch']['productType'];

        $consigment['product'] = $doc['certifiedConsignment']['batch']['product'];

        $consigment['subProduct'] = $doc['certifiedConsignment']['batch']['subProduct'];
        $consigment['productItem'] = $doc['certifiedConsignment']['batch']['productItem'];

        $volume = $doc['certifiedConsignment']['batch']['volume'];

        $consigment['volume'] = (($this->type == self::RETURN_ALL) ? $volume : $this->mb_abs($volume - $this->rejected_data['volume']));

        $consigment['unit'] = $doc['certifiedConsignment']['batch']['unit'];

        if (isset($doc['certifiedConsignment']['batch']['packingList']))
            $consigment['packageList'] = $doc['certifiedConsignment']['batch']['packingList'];

        $consigment['dateOfProduction'] = $doc['certifiedConsignment']['batch']['dateOfProduction'];

        if (isset($doc['certifiedConsignment']['batch']['expiryDate']))
            $consigment['expiryDate'] = $doc['certifiedConsignment']['batch']['expiryDate'];

        $consigment['batchID'] = $doc['certifiedConsignment']['batch']['batchID'];
        $consigment['perishable'] = $doc['certifiedConsignment']['batch']['perishable'];
        $consigment['origin'] = $doc['certifiedConsignment']['batch']['origin'];
        $consigment['lowGradeCargo'] = $doc['certifiedConsignment']['batch']['lowGradeCargo'];

        $retuned['consignment'] = $consigment;

        if (isset($doc['certifiedConsignment']['broker']))
            $retuned['broker'] = $doc['certifiedConsignment']['broker'];

        $retuned['transportInfo'] = $doc['certifiedConsignment']['transportInfo'];
        $retuned['transportStorageType'] = $doc['certifiedConsignment']['transportStorageType'];
        
        $accompanyingForms = [];
        if (isset($doc['referencedDocument'])) {
            $docs = null;
            if (isset($doc['referencedDocument']['type']))
                $docs[] = $doc['referencedDocument'];
            else
                $docs = $doc['referencedDocument'];

            foreach ($docs as $item) {
                if (($item['type'] >= 1) && ($item['type'] <= 5)) {
                    $accompanyingForms['waybill']['issueSeries'] = isset($item['issueSeries']) ? $item['issueSeries'] : null;
                    $accompanyingForms['waybill']['issueNumber'] = $item['issueNumber'];
                    $accompanyingForms['waybill']['issueDate'] = $item['issueDate'];
                    $accompanyingForms['waybill']['type'] = $item['type'];
                    break;
                }
            }
        }

        $accompanyingForms['vetCertificate']['uuid'] = $this->UUID;
        if(isset($doc['authentication']['locationProsperity'])) {
            $authentication['locationProsperity'] = $doc['authentication']['locationProsperity'];
        }
        $authentication['purpose']= ['guid' => $doc['authentication']['purpose']['guid']];
        if(isset($doc['authentication']['cargoInspected'])) {
            $authentication['cargoInspected'] = $doc['authentication']['cargoInspected'];
        }
        $authentication['cargoExpertized'] = $doc['authentication']['cargoExpertized'];

        if(isset($doc['authentication']['animalSpentPeriod'])) {
            $authentication['animalSpentPeriod'] =  $doc['authentication']['animalSpentPeriod'];
        }

        if(isset($doc['authentication']['specialMarks'])) {
            $authentication['specialMarks'] =  $doc['authentication']['specialMarks'];
        }

        //Заполняем условия регионализации при необходимости
        if(isset($this->conditions)) {
            $conditions = null;

            foreach ($this->conditions as $item) {
                if($item != "0") {
                    $r13nClause['condition']['guid'] = $item;
                    $conditions[] = $r13nClause;
                }
            }

            $authentication['r13nClause'] = $conditions;
        }

        $accompanyingForms['vetCertificate']['authentication'] = $authentication;
        $retuned['accompanyingForms'] = $accompanyingForms;

        return $retuned;
    }

    private function mb_abs($number)
    {
        return str_replace('-', '', $number);
    }
}
