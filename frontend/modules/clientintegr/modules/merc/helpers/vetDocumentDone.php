<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.05.2018
 * Time: 13:44
 */

namespace frontend\modules\clientintegr\modules\merc\helpers;

use yii\base\Component;

class vetDocumentDone extends Component
{

    public $soap_namespaces = [ 'xmlns:com="http://api.vetrf.ru/schema/cdm/argus/common"',
                  'xmlns:base="http://api.vetrf.ru/schema/cdm/base"',
                  'xmlns:prod="http://api.vetrf.ru/schema/cdm/argus/production"',
                  'xmlns:vet="http://api.vetrf.ru/schema/cdm/mercury/vet-document"',
                  'xmlns:shp="http://api.vetrf.ru/schema/cdm/argus/shipment"',
                  'xmlns:ent="http://api.vetrf.ru/schema/cdm/cerberus/enterprise"',
                  'xmlns:merc="http://api.vetrf.ru/schema/cdm/mercury/applications"'];

    public $localTransactionId;

    public $doc;
    public $login;
    public $UUID;

    public function getXML()
    {

        $doc = $this->doc;
        $date = \Yii::$app->formatter->asDate('now', 'yyyy-MM-dd').'T'.\Yii::$app->formatter->asTime('now', 'HH:mm:ss');
        $xml = '<merc:processIncomingConsignmentRequest>
                  <merc:localTransactionId>'.$this->localTransactionId.'</merc:localTransactionId>
                  <merc:initiator>
                     <com:login>'.$this->login.'</com:login>
                  </merc:initiator>
                  <merc:delivery>
                     <vet:deliveryDate>'.$date.'</vet:deliveryDate>
                     <vet:consignor>
                        <ent:businessEntity>
                           <base:guid>'.$doc->ns2consignor->entbusinessEntity->bsguid->__toString().'</base:guid>
                           <base:uuid>'.$doc->ns2consignor->entbusinessEntity->bsuuid->__toString().'</base:uuid>
                        </ent:businessEntity>
                        <ent:enterprise>
                           <base:guid>'.$doc->ns2consignor->ententerprise->bsguid->__toString().'</base:guid>
                           <base:uuid>'.$doc->ns2consignor->ententerprise->bsuuid->__toString().'</base:uuid>
                        </ent:enterprise>
                     </vet:consignor>
                     <vet:consignee>
                        <ent:businessEntity>
                           <base:guid>'.$doc->ns2consignee->entbusinessEntity->bsguid->__toString().'</base:guid>
                           <base:uuid>'.$doc->ns2consignee->entbusinessEntity->bsuuid->__toString().'</base:uuid>
                        </ent:businessEntity>
                        <ent:enterprise>
                           <base:guid>'.$doc->ns2consignee->ententerprise->bsguid->__toString().'</base:guid>
                           <base:uuid>'.$doc->ns2consignee->ententerprise->bsuuid->__toString().'</base:uuid>
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
                        <vet:volume>'.$doc->ns2batch->ns2volume.'</vet:volume>
                        <vet:unit>
                           <base:uuid>'.$doc->ns2batch->ns2unit->bsuuid.'</base:uuid>
                        </vet:unit>
                        <vet:packingList>
                           <com:packingForm>
                              <base:uuid>'.$doc->ns2batch->ns2packingList->argcpackingForm->bsuuid->__toString().'</base:uuid>
                           </com:packingForm>
                        </vet:packingList>
                        <vet:packingAmount>'.$doc->ns2batch->ns2packingAmount->__toString().'</vet:packingAmount>
                        <vet:dateOfProduction>'.
                          $this->getDate($doc->ns2batch->ns2dateOfProduction)
                        .'</vet:dateOfProduction>
                        <vet:expiryDate>'.
                            $this->getDate($doc->ns2batch->ns2expiryDate)
                        .'</vet:expiryDate>
                        <vet:perishable>'.$doc->ns2batch->ns2perishable->__toString().'</vet:perishable>
                        <vet:countryOfOrigin>
                           <base:uuid>'.$doc->ns2batch->ns2countryOfOrigin->bsuuid->__toString().'</base:uuid>
                        </vet:countryOfOrigin>';

                        /*<vet:producerList>
                           <ent:producer>
                              <ent:enterprise>
                                 <base:guid>guid</base:guid>
                              </ent:enterprise>
                              <ent:role>PRODUCER</ent:role>
                           </ent:producer>
                        </vet:producerList>*/

                        $xml .= '<vet:productMarkingList>
                           <vet:productMarking>'.$doc->ns2batch->ns2productMarkingList->ns2productMarking->__toString().'</vet:productMarking>
                        </vet:productMarkingList>
                        <vet:lowGradeCargo>'.$doc->ns2batch->ns2lowGradeCargo->__toString().'</vet:lowGradeCargo>
                     </vet:consignment>
                     <vet:accompanyingForms>
                        <vet:waybill>';
                        $xml .= isset($doc->ns2waybillSeries) ? '<shp:issueSeries>'.$doc->ns2waybillSeries->__toString().'</shp:issueSeries>' : '';
                        $xml .= isset($doc->ns2waybillNumber) ? '<shp:issueNumber>'.$doc->ns2waybillNumber->__toString().'</shp:issueNumber>' : '';
                        $xml .= isset($doc->ns2waybillDate) ? '<shp:issueDate>'.$doc->ns2waybillDate->__toString().'</shp:issueDate>' : '';
                        $xml .= isset($doc->ns2waybillType) ? '<shp:type>'.$doc->ns2waybillType->__toString().'</shp:type>' : '';

                           /*<shp:broker>
                              <base:guid>fce1f0e1-218a-11e2-a69b-b499babae7ea</base:guid>
                           </shp:broker>*/

                           $xml .= '<shp:transportInfo>
                              <shp:transportType>'.$doc->ns2transportInfo->shptransportType->__toString().'</shp:transportType>
                              <shp:transportNumber>
                                 <shp:vehicleNumber>'.$doc->ns2transportInfo->shptransportNumber->shpvehicleNumber->__toString().'</shp:vehicleNumber>
                              </shp:transportNumber>
                           </shp:transportInfo>
                           <shp:transportStorageType>'.$doc->ns2transportStorageType->__toString().'</shp:transportStorageType>
                        </vet:waybill>
                        <vet:vetCertificate>
                            <base:uuid>'.$this->UUID.'</base:uuid>';
                           /*<vet:issueSeries>'.$doc->ns2issueSeries->__toString().'</vet:issueSeries>
                           <vet:issueNumber>'.$doc->ns2issueNumber->__toString().'</vet:issueNumber>
                           <vet:issueDate>'.$doc->ns2issueDate->__toString().'</vet:issueDate>
                           <vet:form>'.$doc->ns2form->__toString().'</vet:form>
                           <vet:consignor>
                             <ent:businessEntity>
                                <base:guid>'.$doc->ns2consignor->entbusinessEntity->bsguid->__toString().'</base:guid>
                             </ent:businessEntity>
                             <ent:enterprise>
                                 <base:guid>'.$doc->ns2consignor->ententerprise->bsguid->__toString().'</base:guid>
                             </ent:enterprise>
                           </vet:consignor>
                           <vet:consignee>
                              <ent:businessEntity>
                                <base:guid>'.$doc->ns2consignee->entbusinessEntity->bsguid->__toString().'</base:guid>
                              </ent:businessEntity>
                              <ent:enterprise>
                                 <base:guid>'.$doc->ns2consignee->ententerprise->bsguid->__toString().'</base:guid>
                              </ent:enterprise>
                           </vet:consignee>
                           <vet:batch>
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
                               <vet:volume>'.$doc->ns2batch->ns2volume.'</vet:volume>
                                <vet:unit>
                                   <base:uuid>'.$doc->ns2batch->ns2unit->bsuuid.'</base:uuid>
                                </vet:unit>
                               <vet:packingList>
                                   <com:packingForm>
                                      <base:uuid>'.$doc->ns2batch->ns2packingList->argcpackingForm->bsuuid->__toString().'</base:uuid>
                                   </com:packingForm>
                                </vet:packingList>
                               <vet:packingAmount>'.$doc->ns2batch->ns2packingAmount->__toString().'</vet:packingAmount>
                        <vet:dateOfProduction>'.
                          $this->getDate($doc->ns2batch->ns2dateOfProduction)
                        .'</vet:dateOfProduction>
                        <vet:expiryDate>'.
                            $this->getDate($doc->ns2batch->ns2expiryDate)
                        .'</vet:expiryDate>
                        <vet:perishable>'.$doc->ns2batch->ns2perishable->__toString().'</vet:perishable>
                        <vet:countryOfOrigin>
                           <base:uuid>'.$doc->ns2batch->ns2countryOfOrigin->bsguid->__toString().'</base:uuid>
                        </vet:countryOfOrigin>';
                              /*<vet:producerList>
                                 <ent:producer>
                                    <ent:enterprise>
                                       <base:guid>guid</base:guid>
                                    </ent:enterprise>
                                    <ent:role>PRODUCER</ent:role>
                                 </ent:producer>
                              </vet:producerList>*/

                              /*$xml .= '<vet:productMarkingList>
                                 <vet:productMarking>'.$doc->ns2batch->ns2productMarkingList->ns2productMarking->__toString().'</vet:productMarking>
                              </vet:productMarkingList>
                              <vet:lowGradeCargo>'.$doc->ns2batch->ns2lowGradeCargo->__toString().'</vet:lowGradeCargo>
                           </vet:batch>
                           <vet:purpose>
                              <base:guid>'.$doc->ns2purpose->bsguid->__toString().'</base:guid>
                           </vet:purpose>';
                           /*<vet:broker>
                              <base:guid>fce1f0e1-218a-11e2-a69b-b499babae7ea</base:guid>
                           </vet:broker>*/

                           /*$xml .= '<vet:transportInfo>
                              <shp:transportType>'.$doc->ns2transportInfo->shptransportType->__toString().'</shp:transportType>
                              <shp:transportNumber>
                                 <shp:vehicleNumber>'.$doc->ns2transportInfo->shptransportNumber->shpvehicleNumber->__toString().'</shp:vehicleNumber>
                              </shp:transportNumber>
                           </vet:transportInfo>
                           <vet:transportStorageType>'.$doc->ns2transportStorageType->__toString().'</vet:transportStorageType>
                           <vet:cargoInspected>'.$doc->ns2cargoInspected->__toString().'</vet:cargoInspected>'.
                         //  <vet:cargoExpertized>'.$doc->ns2cargoExpertized->__toString().'</vet:cargoExpertized>
                           '<vet:expertiseInfo>'.$doc->ns2expertiseInfo->__toString().'</vet:expertiseInfo>
                           <vet:confirmedBy>
                              <com:fio>'.$doc->ns2confirmedBy->argcfio->__toString().'</com:fio>
                              <com:post>'.$doc->ns2confirmedBy->argcpost->__toString().'</com:post>
                           </vet:confirmedBy>';
                           //<vet:confirmedDate>'.$doc->ns2confirmedBy->argcpost->__toString().'</vet:confirmedDate>
                           $xml .= '<vet:locationProsperity>'.$doc->ns2locationProsperity->__toString().'</vet:locationProsperity>';
                           /*<vet:precedingVetDocuments>ВСД №5891</vet:precedingVetDocuments>
                           <vet:importPermit>
                              <com:issueNumber>120685</com:issueNumber>
                              <com:issueDate>2019-12-06</com:issueDate>
                           </vet:importPermit>*/
                           /*$xml .= '<vet:specialMarks>'.$doc->ns2specialMarks->__toString().'</vet:specialMarks>
                        </vet:vetCertificate>*/

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
                        <vet:result>CORRESPONDS</vet:result>
                     </vet:vetInspection>
                     <vet:decision>ACCEPT_ALL</vet:decision>
                  </merc:deliveryFacts>
               </merc:processIncomingConsignmentRequest>';
              return $xml;
    }

    public function getDate($date_raw)
    {
        $first_date = '<vet:firstDate>
        <base:year>'.$date_raw->ns2firstDate->bsyear->__toString().'</base:year>
        <base:month>'.$date_raw->ns2firstDate->bsmonth->__toString().'</base:month>
        <base:day>'.$date_raw->ns2firstDate->bsday->__toString().'</base:day>';
        $first_date .= (isset($date_raw->ns2firstDate->bshour)) ? '<base:hour>'.$date_raw->ns2firstDate->bshour->__toString()."</base:hour>" : "";
        $first_date .= '</vet:firstDate>';
        if($date_raw->ns2secondDate)
        {
            $second_date = '<vet:secondDate>
            <base:year>'.$date_raw->ns2secondDate->bsyear->__toString().'</base:year>
            <base:month>'.$date_raw->ns2secondDate->bsmonth->__toString().'</base:month>
            <base:day>'.$date_raw->ns2secondDate->bsday->__toString().'</base:day>';
            $second_date .= (isset($date_raw->ns2secondDate->bshour)) ? '<base:hour>'.$date_raw->ns2secondDate->bshour->__toString()."</base:hour>" : "";
            $second_date .= '</vet:secondDate>';
            return $first_date.' '.$second_date;
        }

        return $first_date;
    }
}