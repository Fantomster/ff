<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

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
class Application{
    var $applicationId;//UUID
    var $status;//ApplicationStatus
    var $serviceId;//NCName
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
}
class ApplicationResultWrapper{
    var $any;//<anyXML>
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
class Mercury
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


?>