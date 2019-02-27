<?php
namespace api_web\modules\integration\modules\vetis\api\mercury;
/**
 * Class GenericEntity
 * @package api_web\modules\integration\modules\vetis\api\mercury
 * v 2.0
 */
class GenericEntity{
    var $uuid;//UUID
}
class GenericVersioningEntity{
    var $guid;//UUID
    var $active;//boolean
    var $last;//boolean
    var $status;//VersionStatus
    var $createDate;//dateTime
    var $updateDate;//dateTime
    var $previous;//UUID
    var $next;//UUID
}
class ListOptions{
    var $count;//nonNegativeInteger
    var $offset;//nonNegativeInteger
}
class DateInterval{
    var $beginDate;//dateTime
    var $endDate;//dateTime
}
class EntityList{
    var $count;//int
    var $total;//long
    var $offset;//int
    var $hasMore;//boolean
}
class FaultInfo{
    var $message;//string
    var $error;//Error
}
class Error{
    var $_;//string
    var $code;//NCName
    var $qualifier;//Identifier
}
class Application{
    var $applicationId;//UUID
    var $status;//ApplicationStatus
    var $serviceId;//Name
    var $issuerId;//UUID
    var $issueDate;//dateTime
    var $rcvDate;//dateTime
    var $prdcRsltDate;//dateTime
    var $data;//ApplicationDataWrapper
    var $result;//ApplicationResultWrapper
    var $errors;//BusinessErrorList
}
class ApplicationDataWrapper{
    var $any;//<anyXML>
    var $encoding;//ContentEncoding
}
class ApplicationResultWrapper{
    var $any;//<anyXML>
    var $encoding;//ContentEncoding
}
class ApplicationData{
}
class ApplicationResultData{
}
class BusinessErrorList{
    var $error;//BusinessError
}
class BusinessError{
    var $_;//Error
}
class submitApplicationRequest{
    var $apiKey;//APIKey
    var $application;//Application
}
class submitApplicationResponse{
    var $application;//Application
}
class receiveApplicationResultRequest{
    var $apiKey;//APIKey
    var $issuerId;//UUID
    var $applicationId;//UUID
}
class receiveApplicationResultResponse{
    var $application;//Application
}
class Purpose{
    var $uuid;//UUID
    var $guid;//UUID
    var $name;//String255
    var $forSubstandard;//boolean
}
class Unit{
    var $uuid;//UUID
    var $guid;//UUID
    var $name;//String255
    var $fullName;//String255
    var $commonUnitGuid;//UUID
    var $factor;//integer
}
class PackingType{
    var $uuid;//UUID
    var $guid;//UUID
    var $globalID;//PackingCodeType
    var $name;//String255
}
class PurposeList{
    var $purpose;//Purpose
}
class UnitList{
    var $unit;//Unit
}
class Country{
    var $uuid;//UUID
    var $guid;//UUID
    var $name;//String255
    var $fullName;//String255
    var $englishName;//String255
    var $code;//Code
    var $code3;//Code3
}
class FederalDistrict{
    var $fullName;//String255
    var $shortName;//String255
    var $abbreviation;//String255
}
class AddressObjectView{
    var $name;//String255
    var $englishName;//String255
    var $view;//String255
    var $regionCode;//string
    var $type;//String255
    var $countryGuid;//UUID
    var $hasStreets;//boolean
}
class Region{
}
class District{
    var $regionGuid;//UUID
}
class Locality{
    var $regionGuid;//UUID
    var $districtGuid;//UUID
    var $cityGuid;//UUID
}
class Street{
    var $localityGuid;//UUID
}
class Address{
    var $country;//Country
    var $federalDistrict;//FederalDistrict
    var $region;//Region
    var $district;//District
    var $locality;//Locality
    var $subLocality;//Locality
    var $street;//Street
    var $house;//String255
    var $building;//String255
    var $room;//String255
    var $postIndex;//String255
    var $postBox;//String255
    var $additionalInfo;//NText
    var $addressView;//NText
    var $enAddressView;//NText
}
class CountryList{
    var $country;//Country
}
class RegionList{
    var $region;//Region
}
class DistrictList{
    var $district;//District
}
class LocalityList{
    var $locality;//Locality
}
class StreetList{
    var $street;//Street
}
class Enterprise{
    var $guid;//UUID
    var $name;//String255
    var $englishName;//String255
    var $type;//EnterpriseType
    var $numberList;//EnterpriseNumberList
    var $address;//Address
    var $activityList;//EnterpriseActivityList
    var $owner;//BusinessEntity
    var $officialRegistration;//EnterpriseOfficialRegistration
}
class EnterpriseActivityList{
    var $activity;//EnterpriseActivity
}
class EnterpriseActivity{
    var $name;//NText
}
class Producer{
    var $enterprise;//Enterprise
    var $role;//EnterpriseRole
}
class ProducerList{
    var $producer;//Producer
}
class EnterpriseNumberList{
    var $enterpriseNumber;//String255
}
class EnterpriseList{
    var $enterprise;//Enterprise
}
class BusinessMember{
    var $businessEntity;//BusinessEntity
    var $enterprise;//Enterprise
    var $globalID;//GLNType
}
class BusinessEntity{
    var $guid;//UUID
    var $type;//BusinessEntityType
    var $name;//String255
    var $incorporationForm;//IncorporationForm
    var $fullName;//String255
    var $fio;//String255
    var $passport;//String255
    var $inn;//String255
    var $kpp;//String255
    var $ogrn;//String255
    var $juridicalAddress;//Address
    var $activityLocation;//activityLocation
}
/*class activityLocation{
    var $globalID;//GLNType
    var $enterprise;//Enterprise
}*/
class IncorporationForm{
    var $name;//String255
    var $code;//String255
    var $shortName;//String255
}
class BusinessEntityList{
    var $businessEntity;//BusinessEntity
}
class ProductItem{
    var $uuid;//UUID
    var $guid;//UUID
    var $globalID;//GTINType
    var $name;//String255
    var $code;//String255
    var $productType;//ProductType
    var $product;//Product
    var $subProduct;//SubProduct
    var $correspondsToGost;//boolean
    var $gost;//String255
    var $producer;//BusinessEntity
    var $tmOwner;//BusinessEntity
    var $producing;//ProductItemProducing
    var $packaging;//Packaging
    var $isPublic;//boolean
}
class Product{
    var $uuid;//UUID
    var $guid;//UUID
    var $name;//String255
    var $code;//String255
    var $englishName;//String255
    var $productType;//ProductType
}
class SubProduct{
    var $uuid;//UUID
    var $guid;//UUID
    var $name;//String255
    var $code;//String255
    var $englishName;//String255
    var $productGuid;//UUID
}
class ProductList{
    var $product;//Product
}
class SubProductList{
    var $subProduct;//SubProduct
}
class ProductItemList{
    var $productItem;//ProductItem
}
class ProductMarks{
    var $_;//String255
    var $class;//ProductMarkingClass
}
class Package{
    var $level;//PackageLevelType
    var $packingType;//PackingType
    var $quantity;//integer
    var $productMarks;//ProductMarks
}
class PackageList{
    var $package;//Package
}
class ComplexDate{
    var $year;//Year
    var $month;//Month
    var $day;//Day
    var $hour;//Hour
    var $minute;//Minute
}
class ProductItemProducing{
    var $location;//Enterprise
}
class Packaging{
    var $packagingType;//PackingType
    var $quantity;//integer
    var $volume;//Decimal
    var $unit;//Unit
}
class Location{
    var $name;//String255
    var $address;//Address
}
class EnterpriseOfficialRegistration{
    var $ID;//GRNType
    var $businessEntity;//BusinessEntity
    var $kpp;//String255
}
class Organization{
    var $ID;//Identifier
    var $name;//String255
    var $address;//Address
}
class Indicator{
    var $name;//String255
}
class AnimalDisease{
    var $name;//String255
}
class ResearchMethod{
    var $name;//String255
}
class MedicinalDrug{
    var $ID;//Identifier
    var $name;//String255
    var $series;//String255
    var $producer;//BusinessMember
}
class RegionalizationCondition{
    var $uuid; //UUID
    var $guid; //UUID
    var $referenceNumber;//Identifier
    var $text;//Text
    var $strict;//boolean
    var $relatedDisease;//AnimalDisease
}
class RegionalizationConditionGroup{
    var $condition;//RegionalizationCondition
}
class RegionalizationRequirement{
    var $relatedDisease;//AnimalDisease
    var $type;//RegionalizationDecision
    var $conditionGroup;//RegionalizationConditionGroup
}
class RegionalizationShippingRule{
    var $referenceNumber;//Identifier
    var $fromR13nStatus;//RegionalizationStatus
    var $toR13nStatus;//RegionalizationStatus
    var $cargoType;//SubProduct
    var $decision;//RegionalizationDecision
    var $requirement;//RegionalizationRequirement
}
class RegionalizationStatus{
    var $relatedDisease;//AnimalDisease
    var $prosperity;//ProsperityType
    var $vaccination;//VaccinationType
}
class RegionalizationRegionStatus{
    var $referenceNumber;//Identifier
    var $r13nZone;//Area
    var $excludedR13nZone;//Area
    var $r13nStatus;//RegionalizationStatus
}
class Area{
}
class RegionalizationConditionList{
    var $condition;//RegionalizationCondition
}
class RegionalizationRegionStatusList{
    var $status;//RegionalizationRegionStatus
}
class RegionalizationShippingRuleList{
    var $rule;//RegionalizationShippingRule
}
class AnimalDiseaseList{
    var $disease;//AnimalDisease
}
class ResearchMethodList{
    var $method;//ResearchMethod
}
class ActivityLocationList{
    var $location;//BusinessMember
}
class VetDocument{
    var $for;//ID
    var $issueSeries;//String255
    var $issueNumber;//String255
    var $issueDate;//date
    var $UUID; //UUID
    var $vetDForm;//VetDocumentForm
    var $vetDType;//VetDocumentType
    var $vetDStatus;//VetDocumentStatus
    var $finalized;//boolean
    var $lastUpdateDate;//dateTime
    var $certifiedBatch;//CertifiedBatch
    var $certifiedConsignment;//CertifiedConsignment
    var $authentication;//VeterinaryAuthentication
    var $precedingVetDocuments;//String255
    var $referencedDocument;//ReferencedDocument
    var $statusChange;//VetDocumentStatusChange
}
class Batch{
    var $productType;//ProductType
    var $product;//Product
    var $subProduct;//SubProduct
    var $productItem;//ProductItem
    var $volume;//Decimal
    var $unit;//Unit
    var $dateOfProduction;//GoodsDate
    var $expiryDate;//GoodsDate
    var $batchID;//Identifier
    var $perishable;//boolean
    var $origin;//BatchOrigin
    var $lowGradeCargo;//boolean
    var $packageList;//PackageList
    var $owner;//BusinessEntity
}
class Consignment{
    var $sourceStockEntry;//StockEntry
    var $id;//ID
    var $partOf;//IDREF

    var $productType;
    var $product;
    var $subProduct;
    var $productItem;
    var $volume;
    var $unit;
    var $dateOfProduction;
    var $expiryDate;
    var $batchID;
    var $perishable;
    var $origin;
    var $lowGradeCargo;
    var $packageList;
}
class RawBatch{
    var $sourceStockEntry;//StockEntry
    var $volume;//Decimal
    var $unit;//Unit
    var $packageList;//PackageList
}
class ProductiveBatch{
    var $id;//ID
}
class Delivery{
    var $deliveryDate;//dateTime
    var $consignor;//BusinessMember
    var $consignee;//BusinessMember
    var $consignment;//Consignment
    var $broker;//BusinessEntity
    var $transportInfo;//TransportInfo
    var $transportStorageType;//TransportationStorageType
    var $shipmentRoute;//ShipmentRoute
    var $accompanyingForms;//ConsignmentDocumentList
}
class DeliveryFactList{
    var $vetCertificatePresence;//DocumentNature
    var $docInspection;//DeliveryInspection
    var $vetInspection;//DeliveryInspection
    var $decision;//DeliveryDecision
}
class DeliveryInspection{
    var $responsible;//User
    var $result;//DeliveryInspectionResult
    var $info;//Text
}
class ConsignmentDocumentList{
    var $waybill;//Waybill
    var $vetCertificate;//VetDocument
    var $relatedDocument;//ReferencedDocument
}
class GoodsDate{
    var $firstDate;//ComplexDate
    var $secondDate;//ComplexDate
    var $informalDate;//String255
}
class VetDocumentList{
    var $vetDocument;//VetDocument
}
class DiscrepancyReport{
    var $issueDate;
    var $reason;//DiscrepancyReason
    var $description;//string
    var $id;//ID
}
class DiscrepancyReason{
    var $name;//String255
}
class StockEntry{
    var $uuid; //UUID
    var $guid; //UUID
    var $entryNumber;//StockEntryNumber
    var $batch;//Batch
    var $vetDocument;//VetDocument
    var $vetEventList;//StockEntryEventList
}
class StockEntryList{
    var $stockEntry;//StockEntry
}
class StockDiscrepancy{
    var $affectedList;//StockEntryList
    var $resultingList;//StockEntryList
    var $reason;//string
    var $id;//ID
}
class StockEntrySearchPattern{
    var $blankFilter;//StockEntryBlankFilter
}
class Document{
    var $name;//String255
    var $form;//String255
    var $issueSeries;//String255
    var $issueNumber;//String255
    var $issueDate;//date
    var $type;//DocumentType
    var $issuer;//Organization
    var $for;//IDREF
    var $qualifier;//Identifier
}
class Waybill{
    var $issueSeries;//String255
    var $issueNumber;//String255
    var $issueDate;//date
    var $type;//DocumentType
    var $consignor;//BusinessMember
    var $consignee;//BusinessMember
    var $broker;//BusinessEntity
    var $transportInfo;//TransportInfo
    var $transportStorageType;//TransportationStorageType
    var $shipmentRoute;//ShipmentRoute

}
class TransportInfo{
    var $transportType;//TransportType
    var $transportNumber;//TransportNumber
}
class TransportNumber{
    var $containerNumber;//String255
    var $wagonNumber;//String255
    var $vehicleNumber;//String255
    var $trailerNumber;//String255
    var $shipName;//String255
    var $flightNumber;//String255
}
class ShipmentRoutePoint{
    var $sqnId;//SequenceNumber
    //var $location;//Location
    var $enterprise;//Enterprise
    var $transshipment;//boolean
    var $nextTransport;//TransportInfo
}
class ShipmentRoute{
    var $routePoint;//ShipmentRoutePoint
}
class ProductionOperation{
    var $operationId;//Identifier
    var $rawBatch;//RawBatch
    var $productiveBatch;//ProductiveBatch
    var $finalizeOperation;//boolean
    var $appliedProcess;//ProcessingProcedure
}
class MergeStockEntriesOperation{
    var $type;//RegisterModificationType
    var $sourceStockEntry;//StockEntry
    var $resultStockEntry;//StockEntry
}
class PSLModificationOperation{
    var $type;//RegisterModificationType
    var $affectedList;//ProductItemList
    var $resultingList;//ProductItemList
}
class BEModificationOperation{
    var $type;//RegisterModificationType
    var $affectedList;//BusinessEntityList
    var $resultingList;//BusinessEntityList
    var $reason;//BEModificationReason
}
class BEActivityLocationsModificationOperation{
    var $type;//RegisterModificationType
    var $businessEntity;//BusinessEntity
    var $activityLocation;//activityLocation
}
class activityLocation{
    var $globalID;//GLNType
    var $enterprise;//Enterprise
}
class ENTModificationOperation{
    var $type;//RegisterModificationType
    var $affectedList;//EnterpriseList
    var $resultingList;//EnterpriseList
    var $reason;//ENTModificationReason
}
class CertifiedBatch{
    var $producer;//BusinessMember
    var $batch;//Batch
}
class CertifiedConsignment{
    var $consignor;//BusinessMember
    var $consignee;//BusinessMember
    var $broker;//BusinessEntity
    var $transportInfo;//TransportInfo
    var $transportStorageType;//TransportationStorageType
    var $shipmentRoute;//ShipmentRoute
    var $batch;//Batch
}
class ReferencedDocument{
    var $relationshipType;//ReferenceType
}
class VeterinaryEvent{
    var $ID;//Identifier
    var $name;//String255
    var $type;//VeterinaryEventType
    var $actualDateTime;//dateTime
    var $location;//Location
    var $enterprise;//Enterprise
    var $operator;//Organization
    var $referencedDocument;//Document
    var $notes;//Text
}
class LaboratoryResearchEvent{
    var $batchID;//Identifier
    var $expertiseID;//String255
    var $indicator;//Indicator
    var $disease;//AnimalDisease
    var $method;//ResearchMethod
    var $result;//ResearchResult
    var $conclusion;//Text
}
class AnimalMedicationEvent{
    var $disease;//AnimalDisease
    var $medicinalDrug;//MedicinalDrug
    var $effectiveBeforeDate;//dateTime
}
class QuarantineEvent{
    var $duration;//positiveInteger
}
class VeterinaryAuthentication{
    var $purpose;//Purpose
    var $cargoInspected;//boolean
    var $cargoExpertized;//ResearchResult
    var $locationProsperity;//String255
    var $animalSpentPeriod;//AnimalSpentPeriod
    var $monthsSpent;//String255
    var $laboratoryResearch;//LaboratoryResearchEvent
    var $quarantine;//QuarantineEvent
    var $immunization;//AnimalMedicationEvent
    var $veterinaryEvent;//VeterinaryEvent
    var $r13nClause;//RegionalizationClause
    var $specialMarks;//Text
}
class BatchOrigin{
    var $productItem;//ProductItem
    var $country;//Country
    var $producer;//Producer
}
class StockEntryEventList{
    var $laboratoryResearch;//LaboratoryResearchEvent
    var $quarantine;//QuarantineEvent
    var $immunization;//AnimalMedicationEvent
    var $veterinaryEvent;//VeterinaryEvent
}
class VetDocumentStatusChange{
    var $status;//VetDocumentStatus
    var $specifiedPerson;//User
    var $actualDateTime;//dateTime
    var $reason;//VetDocumentStatusChangeReason
}
class RegionalizationClause{
    var $condition;//RegionalizationCondition
    var $text;//Text
}
class RouteSectionR13nRules{
    var $sqnId;//SequenceNumber
    var $appliedR13nRule;//RegionalizationShippingRule
}
class ProcessingProcedure{
    var $type;//ProcessingProcedureType
    var $startDateTime;//dateTime
    var $endDateTime;//dateTime
}
class User{
    var $login;//NCName
    var $fio;//String255
    var $birthDate;//date
    var $identity;//Document
    var $snils;//SNILSType
    var $phone;//String255
    var $workPhone;//String255
    var $email;//String255
    var $workEmail;//String255
    var $organization;//Organization
    var $businessEntity;//BusinessEntity
    var $post;//String255
    var $enabled;//boolean
    var $nonExpired;//boolean
    var $nonLocked;//boolean
    var $authorityList;//AuthorityList
    var $workingAreaList;//WorkingAreaList
}
class UserList{
    var $user;//User
}
class AuthorityList{
    var $authority;//UserAuthority
}
class WorkingAreaList{
    var $workingArea;//WorkingArea
}
class UserAuthority{
    var $ID;//NCName
    var $name;//String255
    var $granted;//boolean
}
class WorkingArea{
    var $area;//Area
    var $enterprise;//Enterprise
}
class MercuryApplicationRequest{
    var $localTransactionId;//Identifier
    var $initiator;//User
    var $sessionToken;//OTPToken
}
class ProcessIncomingConsignmentRequest{
    var $localTransactionId;//Identifier
    var $initiator;//User
    var $delivery;//Delivery
    var $deliveryFacts;//DeliveryFactList
    var $discrepancyReport;//DiscrepancyReport
    var $returnedDelivery;//Delivery
}
class ProcessIncomingConsignmentResponse{
    var $stockEntry;//StockEntry
    var $vetDocument;//VetDocument
}
class PrepareOutgoingConsignmentRequest{
    var $localTransactionId;//Identifier
    var $initiator;//User
    var $delivery;//Delivery
}
class PrepareOutgoingConsignmentResponse{
    var $stockEntry;//StockEntry
    var $vetDocument;//VetDocument
}
class RegisterProductionOperationRequest{
    var $productionOperation;//ProductionOperation
    var $vetDocument;//VetDocument
}
class RegisterProductionOperationResponse{
    var $stockEntryList;//StockEntryList
    var $vetDocument;//VetDocument
}
class MergeStockEntriesRequest{
    var $enterprise;//Enterprise
    var $mergeOperation;//MergeStockEntriesOperation
}
class MergeStockEntriesResponse{
    var $stockEntryList;//StockEntryList
}
class WithdrawVetDocumentRequest{
    var $vetDocumentId;//UUID
    var $withdrawReason;//VetDocumentStatusChangeReason
    var $withdrawDate;//dateTime
    var $specifiedPerson;//User
}
class WithdrawVetDocumentResponse{
    var $vetDocument;//VetDocument
    var $stockEntry;//StockEntry
}
class ModifyBusinessEntityRequest{
    var $modificationOperation;//BEModificationOperation
}
class ModifyBusinessEntityResponse{
    var $businessEntity;//BusinessEntity
}
class ModifyEnterpriseRequest{
    var $modificationOperation;//ENTModificationOperation
}
class ModifyEnterpriseResponse{
    var $enterprise;//Enterprise
}
class ModifyActivityLocationsRequest{
    var $modificationOperation;//BEActivityLocationsModificationOperation
}
class ModifyActivityLocationsResponse{
    var $businessEntity;//BusinessEntity
}
class ResolveDiscrepancyRequest{
    var $uuid;//UUID
    var $localTransactionId;
    var $enterprise;//Enterprise
    var $inventoryDate;//dateTime
    var $responsible;//User
    var $stockDiscrepancy;//StockDiscrepancy
    var $discrepancyReport;//DiscrepancyReport
}
class ResolveDiscrepancyResponse{
    var $stockEntryList;//StockEntryList
}
class ModifyProducerStockListRequest{
    var $localTransactionId;
    var $initiator; //User
    var $modificationOperation;//PSLModificationOperation
}
class ModifyProducerStockListResponse{
    var $productItemList;//ProductItemList
}
class GetVetDocumentByUuidRequest{
    var $uuid;//UUID
    var $localTransactionId;
    var $initiator; //User
    var $enterpriseGuid;//UUID
}
class GetVetDocumentByUuidResponse{
    var $vetDocument;//VetDocument
}
class GetVetDocumentListRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $listOptions;//ListOptions
    var $vetDocumentType;//VetDocumentType
    var $vetDocumentStatus;//VetDocumentStatus
    var $enterpriseGuid;//UUID
}
class GetVetDocumentListResponse{
    var $vetDocumentList;//VetDocumentList
}
class GetVetDocumentChangesListRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $listOptions;//ListOptions
    var $updateDateInterval;//DateInterval
    var $vetDocumentStatus;//VetDocumentStatus
    var $enterpriseGuid;//UUID
}
class GetVetDocumentChangesListResponse{
    var $vetDocumentList;//VetDocumentList
}
class GetStockEntryByGuidRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $guid;//UUID
    var $enterpriseGuid;//UUID
}
class GetStockEntryByGuidResponse{
    var $stockEntry;//StockEntry
}
class GetStockEntryByUuidRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $uuid;//UUID
    var $enterpriseGuid;//UUID
}
class GetStockEntryByUuidResponse{
    var $stockEntry;//StockEntry
}
class GetStockEntryChangesListRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $listOptions;//ListOptions
    var $updateDateInterval;//DateInterval
    var $enterpriseGuid;//UUID
}
class GetStockEntryChangesListResponse{
    var $stockEntryList;//StockEntryList
}
class GetStockEntryListRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $listOptions;//ListOptions
    var $enterpriseGuid;//UUID
    var $searchPattern;//StockEntrySearchPattern
}
class GetStockEntryListResponse{
    var $stockEntryList;//StockEntryList
}
class GetStockEntryVersionListRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $listOptions;//ListOptions
    var $guid;//UUID
    var $enterpriseGuid;//UUID
}
class GetStockEntryVersionListResponse{
    var $stockEntryList;//StockEntryList
}
class UpdateTransportMovementDetailsRequest{
    var $deliveryParticipant;//BusinessMember
    var $vetDocumentUuid;//UUID
    var $shipmentRoute;//ShipmentRoute
}
class UpdateTransportMovementDetailsResponse{
    var $vetDocument;//VetDocument
}
class UpdateVeterinaryEventsRequest{
    var $enterprise;//Enterprise
    var $stockEntry;//StockEntry
}
class UpdateVeterinaryEventsResponse{
    var $stockEntry;//StockEntry
}
class CheckShipmentRegionalizationRequest{
    var $initiator;//User
    var $localTransactionId;//Identifier
    var $cargoType;//SubProduct
    var $shipmentRoute;//ShipmentRoute
}
class CheckShipmentRegionalizationResponse{
    var $r13nRouteSection;//RouteSectionR13nRules
}
class AddBusinessEntityUserRequest{
    var $user;//User
}
class AddBusinessEntityUserResponse{
    var $user;//User
}
class GetBusinessEntityUserListRequest{
    var $listOptions;//ListOptions
}
class GetBusinessEntityUserListResponse{
    var $userList;//UserList
}
class GetBusinessEntityUserRequest{
    var $user;//User
}
class GetBusinessEntityUserResponse{
    var $user;//User
}
class UpdateUserAuthoritiesRequest{
    var $user;//User
}
class UpdateUserAuthoritiesResponse{
    var $user;//User
}
class UpdateUserWorkingAreasRequest{
    var $user;//User
}
class UpdateUserWorkingAreasResponse{
    var $user;//User
}
class UnbindBusinessEntityUserRequest{
    var $user;//User
}
class UnbindBusinessEntityUserResponse{
    var $user;//User
}
class GetAppliedUserAuthorityListRequest{
    var $listOptions;//ListOptions
}
class GetAppliedUserAuthorityListResponse{
    var $authorityList;//AuthorityList
}
class Mercury
{
    public $soapClient;

    private static $classmap = array('GenericEntity'=>'GenericEntity'
    ,'GenericVersioningEntity'=>'GenericVersioningEntity'
    ,'ListOptions'=>'ListOptions'
    ,'DateInterval'=>'DateInterval'
    ,'EntityList'=>'EntityList'
    ,'FaultInfo'=>'FaultInfo'
    ,'Error'=>'Error'
    ,'Application'=>'Application'
    ,'ApplicationDataWrapper'=>'ApplicationDataWrapper'
    ,'ApplicationResultWrapper'=>'ApplicationResultWrapper'
    ,'ApplicationData'=>'ApplicationData'
    ,'ApplicationResultData'=>'ApplicationResultData'
    ,'BusinessErrorList'=>'BusinessErrorList'
    ,'BusinessError'=>'BusinessError'
    ,'submitApplicationRequest'=>'submitApplicationRequest'
    ,'submitApplicationResponse'=>'submitApplicationResponse'
    ,'receiveApplicationResultRequest'=>'receiveApplicationResultRequest'
    ,'receiveApplicationResultResponse'=>'receiveApplicationResultResponse'
    ,'Purpose'=>'Purpose'
    ,'Unit'=>'Unit'
    ,'PackingType'=>'PackingType'
    ,'PurposeList'=>'PurposeList'
    ,'UnitList'=>'UnitList'
    ,'Country'=>'Country'
    ,'FederalDistrict'=>'FederalDistrict'
    ,'AddressObjectView'=>'AddressObjectView'
    ,'Region'=>'Region'
    ,'District'=>'District'
    ,'Locality'=>'Locality'
    ,'Street'=>'Street'
    ,'Address'=>'Address'
    ,'CountryList'=>'CountryList'
    ,'RegionList'=>'RegionList'
    ,'DistrictList'=>'DistrictList'
    ,'LocalityList'=>'LocalityList'
    ,'StreetList'=>'StreetList'
    ,'Enterprise'=>'Enterprise'
    ,'EnterpriseActivityList'=>'EnterpriseActivityList'
    ,'EnterpriseActivity'=>'EnterpriseActivity'
    ,'Producer'=>'Producer'
    ,'ProducerList'=>'ProducerList'
    ,'EnterpriseNumberList'=>'EnterpriseNumberList'
    ,'EnterpriseList'=>'EnterpriseList'
    ,'BusinessMember'=>'BusinessMember'
    ,'BusinessEntity'=>'BusinessEntity'
    ,'activityLocation'=>'activityLocation'
    ,'IncorporationForm'=>'IncorporationForm'
    ,'BusinessEntityList'=>'BusinessEntityList'
    ,'ProductItem'=>'ProductItem'
    ,'Product'=>'Product'
    ,'SubProduct'=>'SubProduct'
    ,'ProductList'=>'ProductList'
    ,'SubProductList'=>'SubProductList'
    ,'ProductItemList'=>'ProductItemList'
    ,'ProductMarks'=>'ProductMarks'
    ,'Package'=>'Package'
    ,'PackageList'=>'PackageList'
    ,'ComplexDate'=>'ComplexDate'
    ,'ProductItemProducing'=>'ProductItemProducing'
    ,'Packaging'=>'Packaging'
    ,'Location'=>'Location'
    ,'EnterpriseOfficialRegistration'=>'EnterpriseOfficialRegistration'
    ,'Organization'=>'Organization'
    ,'Indicator'=>'Indicator'
    ,'AnimalDisease'=>'AnimalDisease'
    ,'ResearchMethod'=>'ResearchMethod'
    ,'MedicinalDrug'=>'MedicinalDrug'
    ,'RegionalizationCondition'=>'RegionalizationCondition'
    ,'RegionalizationConditionGroup'=>'RegionalizationConditionGroup'
    ,'RegionalizationRequirement'=>'RegionalizationRequirement'
    ,'RegionalizationShippingRule'=>'RegionalizationShippingRule'
    ,'RegionalizationStatus'=>'RegionalizationStatus'
    ,'RegionalizationRegionStatus'=>'RegionalizationRegionStatus'
    ,'Area'=>'Area'
    ,'RegionalizationConditionList'=>'RegionalizationConditionList'
    ,'RegionalizationRegionStatusList'=>'RegionalizationRegionStatusList'
    ,'RegionalizationShippingRuleList'=>'RegionalizationShippingRuleList'
    ,'AnimalDiseaseList'=>'AnimalDiseaseList'
    ,'ResearchMethodList'=>'ResearchMethodList'
    ,'ActivityLocationList'=>'ActivityLocationList'
    ,'VetDocument'=>'VetDocument'
    ,'Batch'=>'Batch'
    ,'Consignment'=>'Consignment'
    ,'RawBatch'=>'RawBatch'
    ,'ProductiveBatch'=>'ProductiveBatch'
    ,'Delivery'=>'Delivery'
    ,'DeliveryFactList'=>'DeliveryFactList'
    ,'DeliveryInspection'=>'DeliveryInspection'
    ,'ConsignmentDocumentList'=>'ConsignmentDocumentList'
    ,'GoodsDate'=>'GoodsDate'
    ,'VetDocumentList'=>'VetDocumentList'
    ,'DiscrepancyReport'=>'DiscrepancyReport'
    ,'DiscrepancyReason'=>'DiscrepancyReason'
    ,'StockEntry'=>'StockEntry'
    ,'StockEntryList'=>'StockEntryList'
    ,'StockDiscrepancy'=>'StockDiscrepancy'
    ,'StockEntrySearchPattern'=>'StockEntrySearchPattern'
    ,'Document'=>'Document'
    ,'Waybill'=>'Waybill'
    ,'TransportInfo'=>'TransportInfo'
    ,'TransportNumber'=>'TransportNumber'
    ,'ShipmentRoutePoint'=>'ShipmentRoutePoint'
    ,'ShipmentRoute'=>'ShipmentRoute'
    ,'ProductionOperation'=>'ProductionOperation'
    ,'MergeStockEntriesOperation'=>'MergeStockEntriesOperation'
    ,'PSLModificationOperation'=>'PSLModificationOperation'
    ,'BEModificationOperation'=>'BEModificationOperation'
    ,'BEActivityLocationsModificationOperation'=>'BEActivityLocationsModificationOperation'
   // ,'activityLocation'=>'activityLocation'
    ,'ENTModificationOperation'=>'ENTModificationOperation'
    ,'CertifiedBatch'=>'CertifiedBatch'
    ,'CertifiedConsignment'=>'CertifiedConsignment'
    ,'ReferencedDocument'=>'ReferencedDocument'
    ,'VeterinaryEvent'=>'VeterinaryEvent'
    ,'LaboratoryResearchEvent'=>'LaboratoryResearchEvent'
    ,'AnimalMedicationEvent'=>'AnimalMedicationEvent'
    ,'QuarantineEvent'=>'QuarantineEvent'
    ,'VeterinaryAuthentication'=>'VeterinaryAuthentication'
    ,'BatchOrigin'=>'BatchOrigin'
    ,'StockEntryEventList'=>'StockEntryEventList'
    ,'VetDocumentStatusChange'=>'VetDocumentStatusChange'
    ,'RegionalizationClause'=>'RegionalizationClause'
    ,'RouteSectionR13nRules'=>'RouteSectionR13nRules'
    ,'ProcessingProcedure'=>'ProcessingProcedure'
    ,'User'=>'User'
    ,'UserList'=>'UserList'
    ,'AuthorityList'=>'AuthorityList'
    ,'WorkingAreaList'=>'WorkingAreaList'
    ,'UserAuthority'=>'UserAuthority'
    ,'WorkingArea'=>'WorkingArea'
    ,'MercuryApplicationRequest'=>'MercuryApplicationRequest'
    ,'ProcessIncomingConsignmentRequest'=>'ProcessIncomingConsignmentRequest'
    ,'ProcessIncomingConsignmentResponse'=>'ProcessIncomingConsignmentResponse'
    ,'PrepareOutgoingConsignmentRequest'=>'PrepareOutgoingConsignmentRequest'
    ,'PrepareOutgoingConsignmentResponse'=>'PrepareOutgoingConsignmentResponse'
    ,'RegisterProductionOperationRequest'=>'RegisterProductionOperationRequest'
    ,'RegisterProductionOperationResponse'=>'RegisterProductionOperationResponse'
    ,'MergeStockEntriesRequest'=>'MergeStockEntriesRequest'
    ,'MergeStockEntriesResponse'=>'MergeStockEntriesResponse'
    ,'WithdrawVetDocumentRequest'=>'WithdrawVetDocumentRequest'
    ,'WithdrawVetDocumentResponse'=>'WithdrawVetDocumentResponse'
    ,'ModifyBusinessEntityRequest'=>'ModifyBusinessEntityRequest'
    ,'ModifyBusinessEntityResponse'=>'ModifyBusinessEntityResponse'
    ,'ModifyEnterpriseRequest'=>'ModifyEnterpriseRequest'
    ,'ModifyEnterpriseResponse'=>'ModifyEnterpriseResponse'
    ,'ModifyActivityLocationsRequest'=>'ModifyActivityLocationsRequest'
    ,'ModifyActivityLocationsResponse'=>'ModifyActivityLocationsResponse'
    ,'ResolveDiscrepancyRequest'=>'ResolveDiscrepancyRequest'
    ,'ResolveDiscrepancyResponse'=>'ResolveDiscrepancyResponse'
    ,'ModifyProducerStockListRequest'=>'ModifyProducerStockListRequest'
    ,'ModifyProducerStockListResponse'=>'ModifyProducerStockListResponse'
    ,'GetVetDocumentByUuidRequest'=>'GetVetDocumentByUuidRequest'
    ,'GetVetDocumentByUuidResponse'=>'GetVetDocumentByUuidResponse'
    ,'GetVetDocumentListRequest'=>'GetVetDocumentListRequest'
    ,'GetVetDocumentListResponse'=>'GetVetDocumentListResponse'
    ,'GetVetDocumentChangesListRequest'=>'GetVetDocumentChangesListRequest'
    ,'GetVetDocumentChangesListResponse'=>'GetVetDocumentChangesListResponse'
    ,'GetStockEntryByGuidRequest'=>'GetStockEntryByGuidRequest'
    ,'GetStockEntryByGuidResponse'=>'GetStockEntryByGuidResponse'
    ,'GetStockEntryByUuidRequest'=>'GetStockEntryByUuidRequest'
    ,'GetStockEntryByUuidResponse'=>'GetStockEntryByUuidResponse'
    ,'GetStockEntryChangesListRequest'=>'GetStockEntryChangesListRequest'
    ,'GetStockEntryChangesListResponse'=>'GetStockEntryChangesListResponse'
    ,'GetStockEntryListRequest'=>'GetStockEntryListRequest'
    ,'GetStockEntryListResponse'=>'GetStockEntryListResponse'
    ,'GetStockEntryVersionListRequest'=>'GetStockEntryVersionListRequest'
    ,'GetStockEntryVersionListResponse'=>'GetStockEntryVersionListResponse'
    ,'UpdateTransportMovementDetailsRequest'=>'UpdateTransportMovementDetailsRequest'
    ,'UpdateTransportMovementDetailsResponse'=>'UpdateTransportMovementDetailsResponse'
    ,'UpdateVeterinaryEventsRequest'=>'UpdateVeterinaryEventsRequest'
    ,'UpdateVeterinaryEventsResponse'=>'UpdateVeterinaryEventsResponse'
    ,'CheckShipmentRegionalizationRequest'=>'CheckShipmentRegionalizationRequest'
    ,'CheckShipmentRegionalizationResponse'=>'CheckShipmentRegionalizationResponse'
    ,'AddBusinessEntityUserRequest'=>'AddBusinessEntityUserRequest'
    ,'AddBusinessEntityUserResponse'=>'AddBusinessEntityUserResponse'
    ,'GetBusinessEntityUserListRequest'=>'GetBusinessEntityUserListRequest'
    ,'GetBusinessEntityUserListResponse'=>'GetBusinessEntityUserListResponse'
    ,'GetBusinessEntityUserRequest'=>'GetBusinessEntityUserRequest'
    ,'GetBusinessEntityUserResponse'=>'GetBusinessEntityUserResponse'
    ,'UpdateUserAuthoritiesRequest'=>'UpdateUserAuthoritiesRequest'
    ,'UpdateUserAuthoritiesResponse'=>'UpdateUserAuthoritiesResponse'
    ,'UpdateUserWorkingAreasRequest'=>'UpdateUserWorkingAreasRequest'
    ,'UpdateUserWorkingAreasResponse'=>'UpdateUserWorkingAreasResponse'
    ,'UnbindBusinessEntityUserRequest'=>'UnbindBusinessEntityUserRequest'
    ,'UnbindBusinessEntityUserResponse'=>'UnbindBusinessEntityUserResponse'
    ,'GetAppliedUserAuthorityListRequest'=>'GetAppliedUserAuthorityListRequest'
    ,'GetAppliedUserAuthorityListResponse'=>'GetAppliedUserAuthorityListResponse'

    );

    private static function getClassmap()
    {
        $classmap = [];
        foreach (self::$classmap as $key => $value) {
            if (!isset($classmap[$key])) {
                $classmap[$key] = __NAMESPACE__ . '\\' . $value;
            }
        }
        return $classmap;
    }

    function __construct($params = ['url' => '',
        'login' => '',
        'password' => '',
        'exceptions' => true,
        'trace' => true])
    {
        if(!empty($params['url']))
        $this->soapClient = new \SoapClient($params['url'],
            [   "classmap"=>self::getClassmap(),
                'login' => $params['login'],
                'password' =>  $params['password'],
                'exceptions' =>  $params['login'],
                'trace' =>  $params['trace'],
                'exceptions' =>  $params['exceptions']
            ]);
    }

    function submitApplicationRequest(submitApplicationRequest $submitApplicationRequest)
    {

        $submitApplicationResponse = $this->soapClient->submitApplicationRequest($submitApplicationRequest);
        return $submitApplicationResponse;

    }
    function receiveApplicationResult(receiveApplicationResultRequest $receiveApplicationResultRequest)
    {

        $receiveApplicationResultResponse = $this->soapClient->receiveApplicationResult($receiveApplicationResultRequest);
        return $receiveApplicationResultResponse;

    }}


