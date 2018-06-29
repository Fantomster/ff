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
    //private $rejected_data;
    private $localTransactionId;

    public function __construct(array $config = [])
    {
        $this->doc = $config['doc'];
        $this->login = $config['login'];
        $this->UUID = $config['UUID'];
        $this->type = $config['type'];
        $this->rejected_data = $config['rejected_data'];
        $this->localTransactionId = $config['localTransactionId'];

        parent::__construct($config);
    }

    public function getProcessIncomingConsignmentRequest()
    {

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
        $consigment->origin = $this->doc->certifiedConsignment->batch->origin;
        $consigment->lowGradeCargo = $this->doc->certifiedConsignment->batch->lowGradeCargo;

        $data->delivery->consignment = $consigment;

        if(isset($doc->certifiedConsignment->broker))
            $data->delivery->broker = $this->doc->certifiedConsignment->broker;

        $data->delivery->transportInfo = $this->doc->certifiedConsignment->transportInfo;
        $data->delivery->transportStorageType = $this->doc->certifiedConsignment->transportStorageType;

        $doc = $this->doc;


                        if(isset($doc->ns2batch->ns2expiryDate))
                        $xml .='<vet:expiryDate>'.
                            $this->getDate($doc->ns2batch->ns2expiryDate)
                        .'</vet:expiryDate>';
                        $xml .='<vet:perishable>'.$doc->ns2batch->ns2perishable->__toString().'</vet:perishable>
                        <vet:countryOfOrigin>
                           <base:uuid>'.$doc->ns2batch->ns2countryOfOrigin->bsuuid->__toString().'</base:uuid>
                        </vet:countryOfOrigin>';

                        if(isset($doc->ns2batch->ns2producerList))
                        $xml .= '<vet:producerList>
                           <ent:producer>
                              <ent:enterprise>
                                 <base:guid>'.$doc->ns2batch->ns2producerList->entproducer->ententerprise->bsguid.'</base:guid>
                              </ent:enterprise>
                              <ent:role>'.$doc->ns2batch->ns2producerList->entproducer->entrole.'</ent:role>
                           </ent:producer>
                        </vet:producerList>';

                        if (isset($doc->ns2batch->ns2productMarkingList))
                        $xml .= '<vet:productMarkingList>
                           <vet:productMarking>'.$doc->ns2batch->ns2productMarkingList->ns2productMarking->__toString().'</vet:productMarking>
                        </vet:productMarkingList>';

                        $xml .= '<vet:lowGradeCargo>'.$doc->ns2batch->ns2lowGradeCargo->__toString().'</vet:lowGradeCargo>
                     </vet:consignment>
                     <vet:accompanyingForms>
                        <vet:waybill>';
                        $xml .= isset($doc->ns2waybillSeries) ? '<shp:issueSeries>'.$doc->ns2waybillSeries->__toString().'</shp:issueSeries>' : '';
                        $xml .= isset($doc->ns2waybillNumber) ? '<shp:issueNumber>'.$doc->ns2waybillNumber->__toString().'</shp:issueNumber>' : '';
                        $xml .= isset($doc->ns2waybillDate) ? '<shp:issueDate>'.$doc->ns2waybillDate->__toString().'</shp:issueDate>' : '';
                        $xml .= isset($doc->ns2waybillType) ? '<shp:type>'.$doc->ns2waybillType->__toString().'</shp:type>' : '';

                        if(isset($doc->ns2broker))
                            $xml .='<shp:broker>
                              <base:guid>'.$doc->ns2broker->bsguid->__toString().'</base:guid>
                           </shp:broker>';

                           $xml .= '<shp:transportInfo>
                              <shp:transportType>'.$doc->ns2transportInfo->shptransportType->__toString().'</shp:transportType>
                              <shp:transportNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpcontainerNumber))
                                    $xml.= '<shp:containerNumber>'.$doc->ns2transportInfo->shptransportNumber->shpcontainerNumber->__toString().'</shp:containerNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpwagonNumber))
                                    $xml.= '<shp:wagonNumber>'.$doc->ns2transportInfo->shptransportNumber->shpwagonNumber->__toString().'</shp:wagonNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpvehicleNumber))
                                    $xml.= '<shp:vehicleNumber>'.$doc->ns2transportInfo->shptransportNumber->shpvehicleNumber->__toString().'</shp:vehicleNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shptrailerNumber))
                                    $xml.= '<shp:trailerNumber>'.$doc->ns2transportInfo->shptransportNumber->shptrailerNumber->__toString().'</shp:trailerNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpshipName))
                                    $xml.= '<shp:shipName>'.$doc->ns2transportInfo->shptransportNumber->shpshipName->__toString().'</shp:shipName>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpflightNumber))
                                    $xml.= '<shp:flightNumber>'.$doc->ns2transportInfo->shptransportNumber->shpflightNumber->__toString().'</shp:flightNumber>';

                              $xml .= '</shp:transportNumber>
                           </shp:transportInfo>
                           <shp:transportStorageType>'.$doc->ns2transportStorageType->__toString().'</shp:transportStorageType>
                        </vet:waybill>
                        <vet:vetCertificate>
                            <base:uuid>'.$this->UUID.'</base:uuid>';

                  $xml .= '</vet:vetCertificate>
                  </vet:accompanyingForms>
                  </merc:delivery>
                  <merc:deliveryFacts>
                     <vet:vetCertificatePresence>ELECTRONIC</vet:vetCertificatePresence>
                     <vet:docInspection>
                        <vet:responsible>
                           <com:login>'.$this->login.'</com:login>
                        </vet:responsible>
                        <vet:result>CORRESPONDS</vet:result>
                     </vet:docInspection>
                     <vet:vetInspection>
                        <vet:responsible>
                           <com:login>'.$this->login.'</com:login>
                        </vet:responsible>
                        <vet:result>'.(($this->type == self::ACCEPT_ALL) ? 'CORRESPONDS' : 'MISMATCH').'</vet:result>
                     </vet:vetInspection>
                     <vet:decision>'.$this->type.'</vet:decision>
                  </merc:deliveryFacts>';

                 if($this->type != self::ACCEPT_ALL)
                     $xml .= $this->getDiscrepancyReport($doc, $date);
               $xml .= '</merc:processIncomingConsignmentRequest>';
              return $xml;
    }

    public function getDate($date_raw)
    {
        if(isset($date_raw->ns2informalDate))
            return '<vet:informalDate>'.$date_raw->ns2informalDate.'</vet:informalDate>';

        $first_date = '<vet:firstDate>
        <base:year>'.$date_raw->ns2firstDate->bsyear.'</base:year>
        <base:month>'.$date_raw->ns2firstDate->bsmonth.'</base:month>
        <base:day>'.$date_raw->ns2firstDate->bsday.'</base:day>';
        $first_date .= (isset($date_raw->ns2firstDate->bshour)) ? '<base:hour>'.$date_raw->ns2firstDate->bshour."</base:hour>" : "";
        $first_date .= '</vet:firstDate>';
        if($date_raw->ns2secondDate)
        {
            $second_date = '<vet:secondDate>
            <base:year>'.$date_raw->ns2secondDate->bsyear.'</base:year>
            <base:month>'.$date_raw->ns2secondDate->bsmonth.'</base:month>
            <base:day>'.$date_raw->ns2secondDate->bsday.'</base:day>';
            $second_date .= (isset($date_raw->ns2secondDate->bshour)) ? '<base:hour>'.$date_raw->ns2secondDate->bshour."</base:hour>" : "";
            $second_date .= '</vet:secondDate>';
            return $first_date.' '.$second_date;
        }

        return $first_date;
    }

    public function getDiscrepancyReport($doc, $date)
    {
        $xml ='<merc:discrepancyReport>
                     <vet:issueDate>'.\Yii::$app->formatter->asDate('now', 'yyyy-MM-dd').'</vet:issueDate>
                     <vet:reason>
                        <vet:name>'.$this->rejected_data['reason'].'</vet:name>
                     </vet:reason>
                     <vet:description>'.$this->rejected_data['description'].'</vet:description>
                  </merc:discrepancyReport>
                  
                  <merc:returnedDelivery>
                     <vet:deliveryDate>'.$date.'</vet:deliveryDate>
                     <vet:consignor>
                         <ent:businessEntity>
		                           <base:uuid>'.$doc->ns2consignee->entbusinessEntity->bsuuid->__toString().'</base:uuid>
		                           <base:guid>'.$doc->ns2consignee->entbusinessEntity->bsguid->__toString().'</base:guid>
		                        </ent:businessEntity>
		                        <ent:enterprise>
		                           <base:uuid>'.$doc->ns2consignee->ententerprise->bsuuid->__toString().'</base:uuid>
		                           <base:guid>'.$doc->ns2consignee->ententerprise->bsguid->__toString().'</base:guid>
		                        </ent:enterprise>
                     </vet:consignor>
                     <vet:consignee>
                          <ent:businessEntity>
		                           <base:uuid>'.$doc->ns2consignor->entbusinessEntity->bsuuid->__toString().'</base:uuid>
		                           <base:guid>'.$doc->ns2consignor->entbusinessEntity->bsguid->__toString().'</base:guid>
		                        </ent:businessEntity>
		                        <ent:enterprise>
		                           <base:uuid>'.$doc->ns2consignor->ententerprise->bsuuid->__toString().'</base:uuid>
		                           <base:guid>'.$doc->ns2consignor->ententerprise->bsguid->__toString().'</base:guid>
		                        </ent:enterprise>
                     </vet:consignee>
                     <vet:consignment>
                     <vet:productType>'.$doc->ns2batch->ns2productType->__toString().'</vet:productType>
                        <vet:product>
                           <base:uuid>'.$doc->ns2batch->ns2product->bsuuid->__toString().'</base:uuid>
                        </vet:product>
                        <vet:subProduct>
                           <base:uuid>'.$doc->ns2batch->ns2subProduct->bsuuid->__toString().'</base:uuid>
                        </vet:subProduct>
                        <vet:productItem>
                           <prod:name>'.$doc->ns2batch->ns2productItem->prodname->__toString().'</prod:name>
                        </vet:productItem>
                        <vet:volume>'.(($this->type == self::RETURN_ALL) ? $doc->ns2batch->ns2volume : $this->mb_abs($doc->ns2batch->ns2volume - $this->rejected_data['volume'])).'</vet:volume>
                        <vet:unit>
                           <base:uuid>'.$doc->ns2batch->ns2unit->bsuuid.'</base:uuid>
                        </vet:unit>';

                        if(isset($doc->ns2batch->ns2packingList))
                            $xml .= '<vet:packingList>
                           <com:packingForm>
                              <base:uuid>'.$doc->ns2batch->ns2packingList->argcpackingForm->bsuuid->__toString().'</base:uuid>
                           </com:packingForm>
                        </vet:packingList>';

                        $xml .= '<vet:packingAmount>'.$doc->ns2batch->ns2packingAmount->__toString().'</vet:packingAmount>
                        <vet:dateOfProduction>'.
                          $this->getDate($doc->ns2batch->ns2dateOfProduction)
                        .'</vet:dateOfProduction>';
                        if(isset($doc->ns2batch->ns2expiryDate))
                        $xml .= '<vet:expiryDate>'.
                            $this->getDate($doc->ns2batch->ns2expiryDate)
                        .'</vet:expiryDate>';
                        $xml .= '<vet:perishable>'.$doc->ns2batch->ns2perishable->__toString().'</vet:perishable>
                        <vet:countryOfOrigin>
                           <base:uuid>'.$doc->ns2batch->ns2countryOfOrigin->bsuuid->__toString().'</base:uuid>
                        </vet:countryOfOrigin>';

                        if(isset($doc->ns2batch->ns2producerList))
                        $xml .= '<vet:producerList>
                           <ent:producer>
                              <ent:enterprise>
                                 <base:guid>'.$doc->ns2batch->ns2producerList->entproducer->ententerprise->bsguid.'</base:guid>
                              </ent:enterprise>
                              <ent:role>'.$doc->ns2batch->ns2producerList->entproducer->entrole.'</ent:role>
                           </ent:producer>
                        </vet:producerList>';

                        if (isset($doc->ns2batch->ns2productMarkingList))
                            $xml .= '<vet:productMarkingList>
                           <vet:productMarking>'.$doc->ns2batch->ns2productMarkingList->ns2productMarking->__toString().'</vet:productMarking>
                        </vet:productMarkingList>';
                       $xml .= '<vet:lowGradeCargo>'.$doc->ns2batch->ns2lowGradeCargo->__toString().'</vet:lowGradeCargo>
                     </vet:consignment>
                      <vet:accompanyingForms>
                        <vet:waybill>';
                        $xml .= isset($doc->ns2waybillSeries) ? '<shp:issueSeries>'.$doc->ns2waybillSeries->__toString().'</shp:issueSeries>' : '';
                        $xml .= isset($doc->ns2waybillNumber) ? '<shp:issueNumber>'.$doc->ns2waybillNumber->__toString().'</shp:issueNumber>' : '';
                        $xml .= isset($doc->ns2waybillDate) ? '<shp:issueDate>'.$doc->ns2waybillDate->__toString().'</shp:issueDate>' : '';
                        $xml .= isset($doc->ns2waybillType) ? '<shp:type>'.$doc->ns2waybillType->__toString().'</shp:type>' : '';

                        if(isset($doc->ns2broker))
                            $xml .='<shp:broker>
                              <base:guid>'.$doc->ns2broker->bsguid->__toString().'</base:guid>
                           </shp:broker>';

                           $xml .= '<shp:transportInfo>
                              <shp:transportType>'.$doc->ns2transportInfo->shptransportType->__toString().'</shp:transportType>
                              <shp:transportNumber>';
                                if(isset($doc->ns2transportInfo->shptransportNumber->shpcontainerNumber))
                                   $xml.= '<shp:containerNumber>'.$doc->ns2transportInfo->shptransportNumber->shpcontainerNumber->__toString().'</shp:containerNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpwagonNumber))
                                    $xml.= '<shp:wagonNumber>'.$doc->ns2transportInfo->shptransportNumber->shpwagonNumber->__toString().'</shp:wagonNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpvehicleNumber))
                                    $xml.= '<shp:vehicleNumber>'.$doc->ns2transportInfo->shptransportNumber->shpvehicleNumber->__toString().'</shp:vehicleNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shptrailerNumber))
                                    $xml.= '<shp:trailerNumber>'.$doc->ns2transportInfo->shptransportNumber->shptrailerNumber->__toString().'</shp:trailerNumber>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpshipName))
                                    $xml.= '<shp:shipName>'.$doc->ns2transportInfo->shptransportNumber->shpshipName->__toString().'</shp:shipName>';

                                if(isset($doc->ns2transportInfo->shptransportNumber->shpflightNumber))
                                    $xml.= '<shp:flightNumber>'.$doc->ns2transportInfo->shptransportNumber->shpflightNumber->__toString().'</shp:flightNumber>';

                              $xml .= '</shp:transportNumber>
                           </shp:transportInfo>
                           <shp:transportStorageType>'.$doc->ns2transportStorageType->__toString().'</shp:transportStorageType>
                        </vet:waybill>
                        <vet:vetCertificate>
                                   <vet:issueDate>'.\Yii::$app->formatter->asDate('now', 'yyyy-MM-dd').'</vet:issueDate>
                                   <vet:purpose>
                                      <base:guid>'.$doc->ns2purpose->bsguid->__toString().'</base:guid>
                                   </vet:purpose>';

                                   if(isset($doc->ns2cargoInspected))
                                   $xml .= '<vet:cargoInspected>'.$doc->ns2cargoInspected->__toString().'</vet:cargoInspected>';
                                   $xml .= '<vet:cargoExpertized>'.(isset($doc->ns2cargoExpertized) ? $doc->ns2cargoExpertized->__toString(): 'false').'</vet:cargoExpertized>
                                   <vet:confirmedBy>
                                      <com:fio>'.$doc->ns2confirmedBy->argcfio->__toString().'</com:fio>
                                      <com:post>'.$doc->ns2confirmedBy->argcpost->__toString().'</com:post>
                                   </vet:confirmedBy>
                                   <vet:confirmedDate>'.$date.'</vet:confirmedDate>
                                   <vet:locationProsperity>'.$doc->ns2locationProsperity->__toString().'</vet:locationProsperity>
                    </vet:vetCertificate>
                  </vet:accompanyingForms>
                     
                  </merc:returnedDelivery>';

                 return $xml;
    }

    private function mb_abs($number)
    {
        return str_replace('-','',$number);
    }
}
