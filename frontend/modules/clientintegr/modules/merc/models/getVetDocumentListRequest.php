<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

class getVetDocumentListRequest extends BaseRequest
{
    public $localTransactionId;
    public $listOptions;
    public $vetDocumentType;
    public $vetDocumentStatus;

    private $initiator;
    private $enterpriseGuid;
    private $soap_namespaces = ['xmlns:merc="http://api.vetrf.ru/schema/cdm/mercury/applications"'];

    const DOC_TYPE_INCOMMING = 'INCOMING';
    const DOC_TYPE_OUTGOING = 'OUTGOING';
    const DOC_TYPE_PRODUCTIVE = 'PRODUCTIVE';
    const DOC_TYPE_RETURNABLE = 'RETURNABLE';
    const DOC_TYPE_TRANSPORT = 'TRANSPORT';

    public $types = [
        self::DOC_TYPE_INCOMMING => 'Входящий ВСД',
        self::DOC_TYPE_OUTGOING => 'Исходящий ВСД',
        self::DOC_TYPE_PRODUCTIVE => 'Производственный ВСД',
        self::DOC_TYPE_RETURNABLE => 'Возвратный ВСД',
        self::DOC_TYPE_TRANSPORT => 'Транспортный ВСД',
    ];

    const DOC_STATUS_CONFIRMED = 'CONFIRMED';
    const DOC_STATUS_WITHDRAWN = 'WITHDRAWN';
    const DOC_STATUS_UTILIZED = 'UTILIZED';

    public $statuses = [
        self::DOC_STATUS_CONFIRMED => 'Оформлен',
        self::DOC_STATUS_WITHDRAWN => 'Аннулирован',
        self::DOC_STATUS_UTILIZED => 'Погашен',
    ];

    public function rules()
    {
        return [
            [['localTransactionId', 'vetDocumentType', 'vetDocumentStatus', 'enterpriseGuid', 'initiator', '_soap_namespace'], 'safe'],
        ];
    }

    public function getSoap_namespaces()
    {
        return $this->soap_namespaces;
        /*return [  'xmlns:merc="http://api.vetrf.ru/schema/cdm/mercury/applications"',
                  'xmlns:vet="http://api.vetrf.ru/schema/cdm/mercury/vet-document"',
                  'xmlns:base="http://api.vetrf.ru/schema/cdm/base"',
                  'xmlns:com="http://api.vetrf.ru/schema/cdm/argus/common"',
                  'xmlns:ent="http://api.vetrf.ru/schema/cdm/cerberus/enterprise"',
                  'xmlns:ikar="http://api.vetrf.ru/schema/cdm/ikar"'
        ];*/
    }

    public function setInitiator($login)
    {
        $this->initiator = new initiator();
        $this->initiator->login = $login;
        $this->soap_namespaces[] = $this->initiator->soap_namespaces;

    }

    public function setEnterpriseGuid($GUID)
    {
        $this->enterpriseGuid = $GUID;
        $this->soap_namespaces[] = 'xmlns:ent="http://api.vetrf.ru/schema/cdm/cerberus/enterprise"';
    }

    public function getEnterpriseGuid()
    {
        return $this->enterpriseGuid;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    public function getXML()
    {
        $xml = '<merc:getVetDocumentListRequest>'.PHP_EOL.
    '<merc:localTransactionId>' . $this->localTransactionId . '</merc:localTransactionId>'.PHP_EOL;
        if (isset($this->initiator))
            $xml .= $this->initiator->getXML();

       /* $xml .= '<base:listOptions>'.PHP_EOL.
            '<base:count>10</base:count>'.PHP_EOL.
            '<base:offset>0</base:offset>'.PHP_EOL.
        '</base:listOptions>'.PHP_EOL.
        '<vet:vetDocumentType>INCOMING</vet:vetDocumentType>'.PHP_EOL.
        '<vet:vetDocumentStatus>UTILIZED</vet:vetDocumentStatus>'.PHP_EOL;*/

        $xml .= '<ent:enterpriseGuid>'.$this->enterpriseGuid.'</ent:enterpriseGuid>'.PHP_EOL.
        '</merc:getVetDocumentListRequest>';

        return $xml;
    }
}