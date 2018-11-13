<?php
namespace api_web\modules\integration\modules\vetis\api\cerber;

/**
 * Class GenericEntity
 * @package api_web\modules\integration\modules\vetis\api\cerber
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
class Purpose{
var $name;//String255
var $forSubstandard;//boolean
}
class Unit{
var $name;//String255
var $fullName;//String255
var $commonUnitGuid;//UUID
var $factor;//integer
}
class PackingType{
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
var $guid; //UUID
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
class activityLocation{
var $globalID;//GLNType
var $enterprise;//Enterprise
}
class IncorporationForm{
var $name;//String255
var $code;//String255
var $shortName;//String255
}
class BusinessEntityList{
var $businessEntity;//BusinessEntity
}
class ProductItem{
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
var $name;//String255
var $code;//String255
var $englishName;//String255
var $productType;//ProductType
}
class SubProduct{
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
class getPurposeByGuidRequest{
var $guid;//UUID
}
class getPurposeByGuidResponse{
var $purpose;//Purpose
}
class getPurposeByUuidRequest{
var $uuid;//UUID
}
class getPurposeByUuidResponse{
var $purpose;//Purpose
}
class getPurposeListRequest{
var $listOptions;//ListOptions
}
class getPurposeListResponse{
var $purposeList;//PurposeList
}
class getPurposeChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getPurposeChangesListResponse{
var $purposeList;//PurposeList
}
class getUnitByGuidRequest{
var $guid;//UUID
}
class getUnitByGuidResponse{
var $unit;//Unit
}
class getUnitByUuidRequest{
var $uuid;//UUID
}
class getUnitByUuidResponse{
var $unit;//Unit
}
class getUnitListRequest{
var $listOptions;//ListOptions
}
class getUnitListResponse{
var $unitList;//UnitList
}
class getUnitChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getUnitChangesListResponse{
var $unitList;//UnitList
}
class getProductByGuidRequest{
var $guid;//UUID
}
class getProductByGuidResponse{
var $product;//Product
}
class getProductByUuidRequest{
var $uuid;//UUID
}
class getProductByUuidResponse{
var $product;//Product
}
class getProductByTypeListRequest{
var $listOptions;//ListOptions
var $productType;//ProductType
}
class getProductByTypeListResponse{
var $productList;//ProductList
}
class getProductChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getProductChangesListResponse{
var $productList;//ProductList
}
class getSubProductByGuidRequest{
var $guid;//UUID
}
class getSubProductByGuidResponse{
var $subProduct;//SubProduct
}
class getSubProductByUuidRequest{
var $uuid;//UUID
}
class getSubProductByUuidResponse{
var $subProduct;//SubProduct
}
class getSubProductByProductListRequest{
var $listOptions;//ListOptions
var $productGuid;//UUID
}
class getSubProductByProductListResponse{
var $subProductList;//SubProductList
}
class getSubProductChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getSubProductChangesListResponse{
var $subProductList;//SubProductList
}
class getProductItemByGuidRequest{
var $guid;//UUID
}
class getProductItemByGuidResponse{
var $productItem;//ProductItem
}
class getProductItemByUuidRequest{
var $uuid;//UUID
}
class getProductItemByUuidResponse{
var $productItem;//ProductItem
}
class getProductItemListRequest{
var $listOptions;//ListOptions
var $productType;//ProductType
var $product;//Product
var $subProduct;//SubProduct
var $businessEntity;//BusinessEntity
var $enterprise;//Enterprise
}
class getProductItemListResponse{
var $productItemList;//ProductItemList
}
class getProductItemChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
var $businessEntity;//BusinessEntity
var $enterprise;//Enterprise
}
class getProductItemChangesListResponse{
var $productItemList;//ProductItemList
}
class getAllCountryListRequest{
var $listOptions;//ListOptions
}
class getAllCountryListResponse{
var $countryList;//CountryList
}
class getCountryByGuidRequest{
var $guid;//UUID
}
class getCountryByGuidResponse{
var $country;//Country
}
class getCountryByUuidRequest{
var $uuid;//UUID
}
class getCountryByUuidResponse{
var $country;//Country
}
class getCountryChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getCountryChangesListResponse{
var $countryList;//CountryList
}
class getRegionListByCountryRequest{
var $listOptions;//ListOptions
var $countryGuid;//UUID
}
class getRegionListByCountryResponse{
var $regionList;//RegionList
}
class getRegionByGuidRequest{
var $guid;//UUID
}
class getRegionByGuidResponse{
var $region;//Region
}
class getRegionByUuidRequest{
var $uuid;//UUID
}
class getRegionByUuidResponse{
var $region;//Region
}
class getRegionChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getRegionChangesListResponse{
var $regionList;//RegionList
}
class getDistrictListByRegionRequest{
var $listOptions;//ListOptions
var $regionGuid;//UUID
}
class getDistrictListByRegionResponse{
var $districtList;//DistrictList
}
class getDistrictByGuidRequest{
var $guid;//UUID
}
class getDistrictByGuidResponse{
var $district;//District
}
class getDistrictByUuidRequest{
var $uuid;//UUID
}
class getDistrictByUuidResponse{
var $district;//District
}
class getDistrictChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getDistrictChangesListResponse{
var $districtList;//DistrictList
}
class getLocalityListByRegionRequest{
var $listOptions;//ListOptions
var $regionGuid;//UUID
}
class getLocalityListByRegionResponse{
var $localityList;//LocalityList
}
class getLocalityListByDistrictRequest{
var $listOptions;//ListOptions
var $districtGuid;//UUID
}
class getLocalityListByDistrictResponse{
var $localityList;//LocalityList
}
class getLocalityListByLocalityRequest{
var $listOptions;//ListOptions
var $localityGuid;//UUID
}
class getLocalityListByLocalityResponse{
var $localityList;//LocalityList
}
class getStreetListByLocalityRequest{
var $listOptions;//ListOptions
var $localityGuid;//UUID
}
class getStreetListByLocalityResponse{
var $streetList;//StreetList
}
class findLocalityListByNameRequest{
var $listOptions;//ListOptions
var $regionGuid;//UUID
var $pattern;//string
}
class findLocalityListByNameResponse{
var $localityList;//LocalityList
}
class findStreetListByNameRequest{
var $listOptions;//ListOptions
var $localityGuid;//UUID
var $pattern;//string
}
class findStreetListByNameResponse{
var $streetList;//StreetList
}
class getEnterpriseByGuidRequest{
var $guid;//UUID
}
class getEnterpriseByGuidResponse{
var $enterprise;//Enterprise
}
class getEnterpriseByUuidRequest{
var $uuid;//UUID
}
class getEnterpriseByUuidResponse{
var $enterprise;//Enterprise
}
class getForeignEnterpriseListRequest{
var $listOptions;//ListOptions
var $enterpriseGroup;//EnterpriseGroup
var $enterprise;//Enterprise
}
class getForeignEnterpriseListResponse{
var $enterpriseList;//EnterpriseList
}
class getRussianEnterpriseListRequest{
var $listOptions;//ListOptions
var $enterprise;//Enterprise
}
class getRussianEnterpriseListResponse{
var $enterpriseList;//EnterpriseList
}
class getForeignEnterpriseChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getForeignEnterpriseChangesListResponse{
var $enterpriseList;//EnterpriseList
}
class getRussianEnterpriseChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getRussianEnterpriseChangesListResponse{
var $enterpriseList;//EnterpriseList
}
class getBusinessEntityByGuidRequest{
var $guid;//UUID
}
class getBusinessEntityByGuidResponse{
var $businessEntity;//BusinessEntity
}
class getBusinessEntityByUuidRequest{
var $uuid;//UUID
}
class getBusinessEntityByUuidResponse{
var $businessEntity;//BusinessEntity
}
class getBusinessEntityListRequest{
var $listOptions;//ListOptions
var $businessEntity;//BusinessEntity
}
class getBusinessEntityListResponse{
var $businessEntityList;//BusinessEntityList
}
class getBusinessEntityChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getBusinessEntityChangesListResponse{
var $businessEntityList;//BusinessEntityList
}
class getBusinessMemberByGLNRequest{
var $globalID;//GLNType
}
class getBusinessMemberByGLNResponse{
var $businessMember;//BusinessMember
}
class getActivityLocationListRequest{
var $listOptions;//ListOptions
var $businessEntity;//BusinessEntity
}
class getActivityLocationListResponse{
var $activityLocationList;//ActivityLocationList
}
class getR13nConditionListRequest{
var $listOptions;//ListOptions
var $disease;//AnimalDisease
}
class getR13nConditionListResponse{
var $r13nConditionList;//RegionalizationConditionList
}
class getActualR13nRegionStatusListRequest{
var $listOptions;//ListOptions
var $disease;//AnimalDisease
var $r13nZone;//Area
}
class getActualR13nRegionStatusListResponse{
var $r13nRegionStatusList;//RegionalizationRegionStatusList
}
class getActualR13nShippingRuleListRequest{
var $listOptions;//ListOptions
var $disease;//AnimalDisease
var $productType;//ProductType
var $product;//Product
var $subProduct;//SubProduct
}
class getActualR13nShippingRuleListResponse{
var $r13nShippingRuleList;//RegionalizationShippingRuleList
}
class getDiseaseByGuidRequest{
var $guid;//UUID
}
class getDiseaseByGuidResponse{
var $disease;//AnimalDisease
}
class getDiseaseByUuidRequest{
var $uuid;//UUID
}
class getDiseaseByUuidResponse{
var $disease;//AnimalDisease
}
class getDiseaseListRequest{
var $listOptions;//ListOptions
}
class getDiseaseListResponse{
var $diseaseList;//AnimalDiseaseList
}
class getDiseaseChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getDiseaseChangesListResponse{
var $diseaseList;//AnimalDiseaseList
}
class getResearchMethodByGuidRequest{
var $guid;//UUID
}
class getResearchMethodByGuidResponse{
var $researchMethod;//ResearchMethod
}
class getResearchMethodByUuidRequest{
var $uuid;//UUID
}
class getResearchMethodByUuidResponse{
var $researchMethod;//ResearchMethod
}
class getResearchMethodListRequest{
var $listOptions;//ListOptions
}
class getResearchMethodListResponse{
var $researchMethodList;//ResearchMethodList
}
class getResearchMethodChangesListRequest{
var $listOptions;//ListOptions
var $updateDateInterval;//DateInterval
}
class getResearchMethodChangesListResponse{
var $researchMethodList;//ResearchMethodList
}
class Cerber
{
 var $soapClient;

private static $classmap = array('GenericEntity'=>'GenericEntity'
,'GenericVersioningEntity'=>'GenericVersioningEntity'
,'ListOptions'=>'ListOptions'
,'DateInterval'=>'DateInterval'
,'EntityList'=>'EntityList'
,'FaultInfo'=>'FaultInfo'
,'Error'=>'Error'
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
,'getPurposeByGuidRequest'=>'getPurposeByGuidRequest'
,'getPurposeByGuidResponse'=>'getPurposeByGuidResponse'
,'getPurposeByUuidRequest'=>'getPurposeByUuidRequest'
,'getPurposeByUuidResponse'=>'getPurposeByUuidResponse'
,'getPurposeListRequest'=>'getPurposeListRequest'
,'getPurposeListResponse'=>'getPurposeListResponse'
,'getPurposeChangesListRequest'=>'getPurposeChangesListRequest'
,'getPurposeChangesListResponse'=>'getPurposeChangesListResponse'
,'getUnitByGuidRequest'=>'getUnitByGuidRequest'
,'getUnitByGuidResponse'=>'getUnitByGuidResponse'
,'getUnitByUuidRequest'=>'getUnitByUuidRequest'
,'getUnitByUuidResponse'=>'getUnitByUuidResponse'
,'getUnitListRequest'=>'getUnitListRequest'
,'getUnitListResponse'=>'getUnitListResponse'
,'getUnitChangesListRequest'=>'getUnitChangesListRequest'
,'getUnitChangesListResponse'=>'getUnitChangesListResponse'
,'getProductByGuidRequest'=>'getProductByGuidRequest'
,'getProductByGuidResponse'=>'getProductByGuidResponse'
,'getProductByUuidRequest'=>'getProductByUuidRequest'
,'getProductByUuidResponse'=>'getProductByUuidResponse'
,'getProductByTypeListRequest'=>'getProductByTypeListRequest'
,'getProductByTypeListResponse'=>'getProductByTypeListResponse'
,'getProductChangesListRequest'=>'getProductChangesListRequest'
,'getProductChangesListResponse'=>'getProductChangesListResponse'
,'getSubProductByGuidRequest'=>'getSubProductByGuidRequest'
,'getSubProductByGuidResponse'=>'getSubProductByGuidResponse'
,'getSubProductByUuidRequest'=>'getSubProductByUuidRequest'
,'getSubProductByUuidResponse'=>'getSubProductByUuidResponse'
,'getSubProductByProductListRequest'=>'getSubProductByProductListRequest'
,'getSubProductByProductListResponse'=>'getSubProductByProductListResponse'
,'getSubProductChangesListRequest'=>'getSubProductChangesListRequest'
,'getSubProductChangesListResponse'=>'getSubProductChangesListResponse'
,'getProductItemByGuidRequest'=>'getProductItemByGuidRequest'
,'getProductItemByGuidResponse'=>'getProductItemByGuidResponse'
,'getProductItemByUuidRequest'=>'getProductItemByUuidRequest'
,'getProductItemByUuidResponse'=>'getProductItemByUuidResponse'
,'getProductItemListRequest'=>'getProductItemListRequest'
,'getProductItemListResponse'=>'getProductItemListResponse'
,'getProductItemChangesListRequest'=>'getProductItemChangesListRequest'
,'getProductItemChangesListResponse'=>'getProductItemChangesListResponse'
,'getAllCountryListRequest'=>'getAllCountryListRequest'
,'getAllCountryListResponse'=>'getAllCountryListResponse'
,'getCountryByGuidRequest'=>'getCountryByGuidRequest'
,'getCountryByGuidResponse'=>'getCountryByGuidResponse'
,'getCountryByUuidRequest'=>'getCountryByUuidRequest'
,'getCountryByUuidResponse'=>'getCountryByUuidResponse'
,'getCountryChangesListRequest'=>'getCountryChangesListRequest'
,'getCountryChangesListResponse'=>'getCountryChangesListResponse'
,'getRegionListByCountryRequest'=>'getRegionListByCountryRequest'
,'getRegionListByCountryResponse'=>'getRegionListByCountryResponse'
,'getRegionByGuidRequest'=>'getRegionByGuidRequest'
,'getRegionByGuidResponse'=>'getRegionByGuidResponse'
,'getRegionByUuidRequest'=>'getRegionByUuidRequest'
,'getRegionByUuidResponse'=>'getRegionByUuidResponse'
,'getRegionChangesListRequest'=>'getRegionChangesListRequest'
,'getRegionChangesListResponse'=>'getRegionChangesListResponse'
,'getDistrictListByRegionRequest'=>'getDistrictListByRegionRequest'
,'getDistrictListByRegionResponse'=>'getDistrictListByRegionResponse'
,'getDistrictByGuidRequest'=>'getDistrictByGuidRequest'
,'getDistrictByGuidResponse'=>'getDistrictByGuidResponse'
,'getDistrictByUuidRequest'=>'getDistrictByUuidRequest'
,'getDistrictByUuidResponse'=>'getDistrictByUuidResponse'
,'getDistrictChangesListRequest'=>'getDistrictChangesListRequest'
,'getDistrictChangesListResponse'=>'getDistrictChangesListResponse'
,'getLocalityListByRegionRequest'=>'getLocalityListByRegionRequest'
,'getLocalityListByRegionResponse'=>'getLocalityListByRegionResponse'
,'getLocalityListByDistrictRequest'=>'getLocalityListByDistrictRequest'
,'getLocalityListByDistrictResponse'=>'getLocalityListByDistrictResponse'
,'getLocalityListByLocalityRequest'=>'getLocalityListByLocalityRequest'
,'getLocalityListByLocalityResponse'=>'getLocalityListByLocalityResponse'
,'getStreetListByLocalityRequest'=>'getStreetListByLocalityRequest'
,'getStreetListByLocalityResponse'=>'getStreetListByLocalityResponse'
,'findLocalityListByNameRequest'=>'findLocalityListByNameRequest'
,'findLocalityListByNameResponse'=>'findLocalityListByNameResponse'
,'findStreetListByNameRequest'=>'findStreetListByNameRequest'
,'findStreetListByNameResponse'=>'findStreetListByNameResponse'
,'getEnterpriseByGuidRequest'=>'getEnterpriseByGuidRequest'
,'getEnterpriseByGuidResponse'=>'getEnterpriseByGuidResponse'
,'getEnterpriseByUuidRequest'=>'getEnterpriseByUuidRequest'
,'getEnterpriseByUuidResponse'=>'getEnterpriseByUuidResponse'
,'getForeignEnterpriseListRequest'=>'getForeignEnterpriseListRequest'
,'getForeignEnterpriseListResponse'=>'getForeignEnterpriseListResponse'
,'getRussianEnterpriseListRequest'=>'getRussianEnterpriseListRequest'
,'getRussianEnterpriseListResponse'=>'getRussianEnterpriseListResponse'
,'getForeignEnterpriseChangesListRequest'=>'getForeignEnterpriseChangesListRequest'
,'getForeignEnterpriseChangesListResponse'=>'getForeignEnterpriseChangesListResponse'
,'getRussianEnterpriseChangesListRequest'=>'getRussianEnterpriseChangesListRequest'
,'getRussianEnterpriseChangesListResponse'=>'getRussianEnterpriseChangesListResponse'
,'getBusinessEntityByGuidRequest'=>'getBusinessEntityByGuidRequest'
,'getBusinessEntityByGuidResponse'=>'getBusinessEntityByGuidResponse'
,'getBusinessEntityByUuidRequest'=>'getBusinessEntityByUuidRequest'
,'getBusinessEntityByUuidResponse'=>'getBusinessEntityByUuidResponse'
,'getBusinessEntityListRequest'=>'getBusinessEntityListRequest'
,'getBusinessEntityListResponse'=>'getBusinessEntityListResponse'
,'getBusinessEntityChangesListRequest'=>'getBusinessEntityChangesListRequest'
,'getBusinessEntityChangesListResponse'=>'getBusinessEntityChangesListResponse'
,'getBusinessMemberByGLNRequest'=>'getBusinessMemberByGLNRequest'
,'getBusinessMemberByGLNResponse'=>'getBusinessMemberByGLNResponse'
,'getActivityLocationListRequest'=>'getActivityLocationListRequest'
,'getActivityLocationListResponse'=>'getActivityLocationListResponse'
,'getR13nConditionListRequest'=>'getR13nConditionListRequest'
,'getR13nConditionListResponse'=>'getR13nConditionListResponse'
,'getActualR13nRegionStatusListRequest'=>'getActualR13nRegionStatusListRequest'
,'getActualR13nRegionStatusListResponse'=>'getActualR13nRegionStatusListResponse'
,'getActualR13nShippingRuleListRequest'=>'getActualR13nShippingRuleListRequest'
,'getActualR13nShippingRuleListResponse'=>'getActualR13nShippingRuleListResponse'
,'getDiseaseByGuidRequest'=>'getDiseaseByGuidRequest'
,'getDiseaseByGuidResponse'=>'getDiseaseByGuidResponse'
,'getDiseaseByUuidRequest'=>'getDiseaseByUuidRequest'
,'getDiseaseByUuidResponse'=>'getDiseaseByUuidResponse'
,'getDiseaseListRequest'=>'getDiseaseListRequest'
,'getDiseaseListResponse'=>'getDiseaseListResponse'
,'getDiseaseChangesListRequest'=>'getDiseaseChangesListRequest'
,'getDiseaseChangesListResponse'=>'getDiseaseChangesListResponse'
,'getResearchMethodByGuidRequest'=>'getResearchMethodByGuidRequest'
,'getResearchMethodByGuidResponse'=>'getResearchMethodByGuidResponse'
,'getResearchMethodByUuidRequest'=>'getResearchMethodByUuidRequest'
,'getResearchMethodByUuidResponse'=>'getResearchMethodByUuidResponse'
,'getResearchMethodListRequest'=>'getResearchMethodListRequest'
,'getResearchMethodListResponse'=>'getResearchMethodListResponse'
,'getResearchMethodChangesListRequest'=>'getResearchMethodChangesListRequest'
,'getResearchMethodChangesListResponse'=>'getResearchMethodChangesListResponse'

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

function GetBusinessEntityByGuid(getBusinessEntityByGuidRequest $getBusinessEntityByGuidRequest)
{

$getBusinessEntityByGuidResponse = $this->soapClient->GetBusinessEntityByGuid($getBusinessEntityByGuidRequest);
return $getBusinessEntityByGuidResponse;

}
function GetBusinessEntityByUuid(getBusinessEntityByUuidRequest $getBusinessEntityByUuidRequest)
{

$getBusinessEntityByUuidResponse = $this->soapClient->GetBusinessEntityByUuid($getBusinessEntityByUuidRequest);
return $getBusinessEntityByUuidResponse;

}
function GetBusinessEntityList(getBusinessEntityListRequest $getBusinessEntityListRequest)
{

$getBusinessEntityListResponse = $this->soapClient->GetBusinessEntityList($getBusinessEntityListRequest);
return $getBusinessEntityListResponse;

}
function GetBusinessEntityChangesList(getBusinessEntityChangesListRequest $getBusinessEntityChangesListRequest)
{

$getBusinessEntityChangesListResponse = $this->soapClient->GetBusinessEntityChangesList($getBusinessEntityChangesListRequest);
return $getBusinessEntityChangesListResponse;

}
function GetEnterpriseByGuid(getEnterpriseByGuidRequest $getEnterpriseByGuidRequest)
{

$getEnterpriseByGuidResponse = $this->soapClient->GetEnterpriseByGuid($getEnterpriseByGuidRequest);
return $getEnterpriseByGuidResponse;

}
function GetEnterpriseByUuid(getEnterpriseByUuidRequest $getEnterpriseByUuidRequest)
{

$getEnterpriseByUuidResponse = $this->soapClient->GetEnterpriseByUuid($getEnterpriseByUuidRequest);
return $getEnterpriseByUuidResponse;

}
function GetForeignEnterpriseList(getForeignEnterpriseListRequest $getForeignEnterpriseListRequest)
{

$getForeignEnterpriseListResponse = $this->soapClient->GetForeignEnterpriseList($getForeignEnterpriseListRequest);
return $getForeignEnterpriseListResponse;

}
function GetRussianEnterpriseList(getRussianEnterpriseListRequest $getRussianEnterpriseListRequest)
{

$getRussianEnterpriseListResponse = $this->soapClient->GetRussianEnterpriseList($getRussianEnterpriseListRequest);
return $getRussianEnterpriseListResponse;

}
function GetForeignEnterpriseChangesList(getForeignEnterpriseChangesListRequest $getForeignEnterpriseChangesListRequest)
{

$getForeignEnterpriseChangesListResponse = $this->soapClient->GetForeignEnterpriseChangesList($getForeignEnterpriseChangesListRequest);
return $getForeignEnterpriseChangesListResponse;

}
function GetRussianEnterpriseChangesList(getRussianEnterpriseChangesListRequest $getRussianEnterpriseChangesListRequest)
{

$getRussianEnterpriseChangesListResponse = $this->soapClient->GetRussianEnterpriseChangesList($getRussianEnterpriseChangesListRequest);
return $getRussianEnterpriseChangesListResponse;

}
function GetBusinessMemberByGLN(getBusinessMemberByGLNRequest $getBusinessMemberByGLNRequest)
{

$getBusinessMemberByGLNResponse = $this->soapClient->GetBusinessMemberByGLN($getBusinessMemberByGLNRequest);
return $getBusinessMemberByGLNResponse;

}
function GetActivityLocationList(getActivityLocationListRequest $getActivityLocationListRequest)
{

$getActivityLocationListResponse = $this->soapClient->GetActivityLocationList($getActivityLocationListRequest);
return $getActivityLocationListResponse;

}}


?>
