<?php

namespace common\components\edi;

class RelationsInput{
    var $Name;//string
    var $Password;//string
}
class RelationsResponse{
    var $Res;//int
    var $Cnt;//Cnt
}
class Cnt{
    var $relation_response;//relation_response
    var $document_status_response;//document_status_response
    var $mailbox_response;//mailbox_response
}
class relation_response{
    var $relation;//relation
}
class relation{
    var $relation_id;//string
var $direction;//DocumentDirection
var $partner_name;//string
var $partner_iln;//string
var $document_version;//string
var $document_type;//string
var $document_type_id;//string
var $document_test;//DocumentTest
var $document_standard;//string
var $description;//string
}
class ListPBInput{
    var $Name;//string
    var $Password;//string
    var $RelationId;//string
    var $DocumentStatus;//DocumentStatus
    var $TrackingId;//long
    var $DateFrom;//date
    var $DateTo;//date
    var $ItemFrom;//unsignedLong
    var $PageSize;//int
}
class ListPBResponse{
    var $Res;//int
    var $Cnt;//Cnt
}

class document_status_response{
    var $document_status_item;//document_status_item
}
class document_status_item{
    var $tracking_id;//string
    var $partner_iln;//string
    var $document_type;//string
    var $document_number;//string
    var $document_date;//dateTime
    var $submission_date;//string
    var $document_status_description;//string
    var $document_status;//DocumentStatus
    var $document_status_date;//string
    var $document_test;//DocumentTest
    var $document_standard;//string
    var $document_version;//string
    var $control_number;//string
    var $submission_handle;//string
}
class ListMBInput{
    var $Name;//string
    var $Password;//string
    var $RelationId;//string
    var $DocumentStatus;//DocumentStatus
    var $DeliveryPointGln;//string
    var $DocDateFrom;//date
    var $DocDateTo;//date
    var $DateFrom;//date
    var $DateTo;//date
    var $ItemFrom;//unsignedLong
    var $PageSize;//int
}
class ListMBResponse{
    var $Res;//int
    var $Cnt;//Cnt
}

class mailbox_response{
    var $document_info;//document_info
}
class document_info{
    var $tracking_id;//string
    var $partner_iln;//string
    var $document_type;//string
    var $document_number;//string
    var $document_date;//dateTime
    var $recieve_date;//date
    var $receive_datetime;//dateTime
    var $document_status;//DocumentStatus
    var $document_test;//DocumentTest
    var $document_standard;//string
    var $document_control_number;//string
    var $document_version;//string
}
class ReceiveInput{
    var $Name;//string
    var $Password;//string
    var $RelationId;//string
    var $TrackingId;//string
    var $DocumentStatus;//DocumentStatus
}
class ReceiveResponse{
    var $Res;//int
    var $Cnt;//base64Binary
}
class SendResponse{
    var $Res;//int
    var $Cnt;//string
}
class SendInput{
    var $Name;//string
    var $Password;//string
    var $RelationId;//string
    var $DocumentContent;//base64Binary
}
class DocStatusResponse{
    var $Res;//int
    var $Cnt;//string
}
class DocStatusInput{
    var $Name;//string
    var $Password;//string
    var $Status;//DocumentStatus
    var $TrackingId;//string
}
class EdiClass
{
    var $soapClient;

    private static $classmap = array('RelationsInput'=>'RelationsInput'
    ,'RelationsResponse'=>'RelationsResponse'
    ,'Cnt'=>'Cnt'
    ,'relation_response'=>'relation_response'
    ,'relation'=>'relation'
    ,'ListPBInput'=>'ListPBInput'
    ,'ListPBResponse'=>'ListPBResponse'
    ,'document_status_response'=>'document_status_response'
    ,'document_status_item'=>'document_status_item'
    ,'ListMBInput'=>'ListMBInput'
    ,'ListMBResponse'=>'ListMBResponse'
    ,'mailbox_response'=>'mailbox_response'
    ,'document_info'=>'document_info'
    ,'ReceiveInput'=>'ReceiveInput'
    ,'ReceiveResponse'=>'ReceiveResponse'
    ,'SendResponse'=>'SendResponse'
    ,'SendInput'=>'SendInput'
    ,'DocStatusResponse'=>'DocStatusResponse'
    ,'DocStatusInput'=>'DocStatusInput'

    );

    function __construct($url='https://edi-ws.esphere.ru/edi.wsdl')
    {
        $this->soapClient = new \SoapClient($url,array("classmap"=>self::$classmap,"trace" => true,"exceptions" => true));
    }

    function RelationsResponse(RelationsInput $RelationsInput)
    {

        $RelationsResponse = $this->soapClient->process($RelationsInput);
        return $RelationsResponse;

    }
    function ListMBResponse(ListMBInput $ListMBInput)
    {

        $ListMBResponse = $this->soapClient->process($ListMBInput);
        return $ListMBResponse;

    }
    function ListPBResponse(ListPBInput $ListPBInput)
    {

        $ListPBResponse = $this->soapClient->process($ListPBInput);
        return $ListPBResponse;

    }
    function ReceiveResponse(ReceiveInput $ReceiveInput)
    {

        $ReceiveResponse = $this->soapClient->process($ReceiveInput);
        return $ReceiveResponse;

    }
    function SendResponse(SendInput $SendInput)
    {
        $SendResponse = $this->soapClient->process($SendInput);
        return $SendResponse;

    }
    function DocStatusResponse(DocStatusInput $DocStatusInput)
    {

        $DocStatusResponse = $this->soapClient->process($DocStatusInput);
        return $DocStatusResponse;

    }}


?>