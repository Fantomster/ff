<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\product;

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
class ComplexDate{
    var $year;//Year
    var $month;//Month
    var $day;//Day
    var $hour;//Hour
}
class Country{
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
    var $addressView;//String255
    var $enAddressView;//String255
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
}
class ActivityLocation{
    var $enterprise;//Enterprise
}
class EnterpriseActivityList{
    var $activity;//EnterpriseActivity
}
class EnterpriseActivity{
    var $name;//Text
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
    var $activityLocation;//ActivityLocation
}
class IncorporationForm{
    var $name;//String255
    var $code;//String255
    var $shortName;//String255
}
class BusinessEntityList{
    var $businessEntity;//BusinessEntity
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
    var $enterpriseList;//EnterpriseList
}
class ENTModificationOperation{
    var $type;//RegisterModificationType
    var $affectedList;//EnterpriseList
    var $resultingList;//EnterpriseList
    var $reason;//ENTModificationReason
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
class Document{
    var $name;//String255
    var $form;//String255
    var $issueSeries;//String255
    var $issueNumber;//String255
    var $issueDate;//date
}
class User{
    var $fio;//String255
    var $post;//String255
    var $phone;//String255
    var $email;//String255
    var $login;//NCName
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
class PackingForm{
    var $name;//String255
}
class PurposeList{
    var $purpose;//Purpose
}
class UnitList{
    var $unit;//Unit
}
class PackingFormList{
    var $packingForm;//PackingForm
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
class ProductItem{
    var $name;//String255
    var $code;//String255
    var $productType;//ProductType
    var $product;//Product
    var $subProduct;//SubProduct
    var $correspondToGost;//boolean
    var $gost;//String255
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
class PSLModificationOperation{
    var $type;//RegisterModificationType
    var $enterprise;//Enterprise
    var $affectedList;//ProductItemList
    var $resultingList;//ProductItemList
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
    var $subProductGuid;//UUID
    var $enterpriseGuid;//UUID
}
class getProductItemListResponse{
    var $productItemList;//ProductItemList
}
class getProductItemChangesListRequest{
    var $listOptions;//ListOptions
    var $updateDateInterval;//DateInterval
    var $enterpriseGuid;//UUID
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
class Products
{
    var $soapClient;

    private static $classmap = array('GenericEntity'=>'GenericEntity'
    ,'GenericVersioningEntity'=>'GenericVersioningEntity'
    ,'ListOptions'=>'ListOptions'
    ,'DateInterval'=>'DateInterval'
    ,'EntityList'=>'EntityList'
    ,'FaultInfo'=>'FaultInfo'
    ,'Error'=>'Error'
    ,'ComplexDate'=>'ComplexDate'
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
    ,'ActivityLocation'=>'ActivityLocation'
    ,'EnterpriseActivityList'=>'EnterpriseActivityList'
    ,'EnterpriseActivity'=>'EnterpriseActivity'
    ,'Producer'=>'Producer'
    ,'ProducerList'=>'ProducerList'
    ,'EnterpriseNumberList'=>'EnterpriseNumberList'
    ,'EnterpriseList'=>'EnterpriseList'
    ,'BusinessMember'=>'BusinessMember'
    ,'BusinessEntity'=>'BusinessEntity'
    ,'IncorporationForm'=>'IncorporationForm'
    ,'BusinessEntityList'=>'BusinessEntityList'
    ,'BEModificationOperation'=>'BEModificationOperation'
    ,'BEActivityLocationsModificationOperation'=>'BEActivityLocationsModificationOperation'
    ,'ENTModificationOperation'=>'ENTModificationOperation'
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
    ,'Document'=>'Document'
    ,'User'=>'User'
    ,'Purpose'=>'Purpose'
    ,'Unit'=>'Unit'
    ,'PackingForm'=>'PackingForm'
    ,'PurposeList'=>'PurposeList'
    ,'UnitList'=>'UnitList'
    ,'PackingFormList'=>'PackingFormList'
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
    ,'ProductItem'=>'ProductItem'
    ,'Product'=>'Product'
    ,'SubProduct'=>'SubProduct'
    ,'ProductList'=>'ProductList'
    ,'SubProductList'=>'SubProductList'
    ,'ProductItemList'=>'ProductItemList'
    ,'PSLModificationOperation'=>'PSLModificationOperation'
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
    ,'getBusinessEntityByGuidRequest'=>'getBusinessEntityByGuidRequest'
    ,'getBusinessEntityByGuidResponse'=>'getBusinessEntityByGuidResponse'
    ,'getBusinessEntityByUuidRequest'=>'getBusinessEntityByUuidRequest'
    ,'getBusinessEntityByUuidResponse'=>'getBusinessEntityByUuidResponse'
    ,'getBusinessEntityListRequest'=>'getBusinessEntityListRequest'
    ,'getBusinessEntityListResponse'=>'getBusinessEntityListResponse'
    ,'getBusinessEntityChangesListRequest'=>'getBusinessEntityChangesListRequest'
    ,'getBusinessEntityChangesListResponse'=>'getBusinessEntityChangesListResponse'

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


    function __construct($params = ['url' => 'http://api.vetrf.ru/schema/platform/services/2.0-last/EnterpriseService_v2.0_pilot.wsdl',
        'login' => '',
        'password' => '',
        'exceptions' => true,
        'trace' => true])
    {
        $this->soapClient = new \SoapClient($params['url'],
            [   "classmap"=>self::getClassmap(),
                'login' => $params['login'],
                'password' =>  $params['password'],
                'exceptions' =>  $params['login'],
                'trace' =>  $params['trace'],
                'exceptions' =>  $params['exceptions']
            ]);
    }

    function GetProductByGuid(getProductByGuidRequest $getProductByGuidRequest)
    {

        $getProductByGuidResponse = $this->soapClient->GetProductByGuid($getProductByGuidRequest);
        return $getProductByGuidResponse;

    }
    function GetProductByUuid(getProductByUuidRequest $getProductByUuidRequest)
    {

        $getProductByUuidResponse = $this->soapClient->GetProductByUuid($getProductByUuidRequest);
        return $getProductByUuidResponse;

    }
    function GetProductByTypeList(getProductByTypeListRequest $getProductByTypeListRequest)
    {

        $getProductByTypeListResponse = $this->soapClient->GetProductByTypeList($getProductByTypeListRequest);
        return $getProductByTypeListResponse;

    }
    function GetProductChangesList(getProductChangesListRequest $getProductChangesListRequest)
    {

        $getProductChangesListResponse = $this->soapClient->GetProductChangesList($getProductChangesListRequest);
        return $getProductChangesListResponse;

    }
    function GetSubProductByGuid(getSubProductByGuidRequest $getSubProductByGuidRequest)
    {

        $getSubProductByGuidResponse = $this->soapClient->GetSubProductByGuid($getSubProductByGuidRequest);
        return $getSubProductByGuidResponse;

    }
    function GetSubProductByUuid(getSubProductByUuidRequest $getSubProductByUuidRequest)
    {

        $getSubProductByUuidResponse = $this->soapClient->GetSubProductByUuid($getSubProductByUuidRequest);
        return $getSubProductByUuidResponse;

    }
    function GetSubProductByProductList(getSubProductByProductListRequest $getSubProductByProductListRequest)
    {

        $getSubProductByProductListResponse = $this->soapClient->GetSubProductByProductList($getSubProductByProductListRequest);
        return $getSubProductByProductListResponse;

    }
    function GetSubProductChangesList(getSubProductChangesListRequest $getSubProductChangesListRequest)
    {

        $getSubProductChangesListResponse = $this->soapClient->GetSubProductChangesList($getSubProductChangesListRequest);
        return $getSubProductChangesListResponse;

    }
    function GetProductItemByGuid(getProductItemByGuidRequest $getProductItemByGuidRequest)
    {

        $getProductItemByGuidResponse = $this->soapClient->GetProductItemByGuid($getProductItemByGuidRequest);
        return $getProductItemByGuidResponse;

    }
    function GetProductItemByUuid(getProductItemByUuidRequest $getProductItemByUuidRequest)
    {

        $getProductItemByUuidResponse = $this->soapClient->GetProductItemByUuid($getProductItemByUuidRequest);
        return $getProductItemByUuidResponse;

    }
    function GetProductItemList(getProductItemListRequest $getProductItemListRequest)
    {

        $getProductItemListResponse = $this->soapClient->GetProductItemList($getProductItemListRequest);
        return $getProductItemListResponse;

    }
    function GetProductItemChangesList(getProductItemChangesListRequest $getProductItemChangesListRequest)
    {

        $getProductItemChangesListResponse = $this->soapClient->GetProductItemChangesList($getProductItemChangesListRequest);
        return $getProductItemChangesListResponse;

    }}


?>