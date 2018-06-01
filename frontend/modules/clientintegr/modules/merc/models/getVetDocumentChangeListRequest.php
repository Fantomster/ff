<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

class getVetDocumentChangeListRequest extends BaseRequest
{
    public $localTransactionId;
    public $listOptions;
    public $date_start;
    public $date_end;

    private $initiator;
    private $enterpriseGuid;
    private $soap_namespaces = ['xmlns:merc="http://api.vetrf.ru/schema/cdm/mercury/applications"', 'xmlns:vet="http://api.vetrf.ru/schema/cdm/mercury/vet-document"', 'xmlns:base="http://api.vetrf.ru/schema/cdm/base"'];

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
            [['localTransactionId', 'enterpriseGuid', 'initiator', '_soap_namespace', 'date_start', 'date_end'], 'safe'],
        ];
    }

    public function getSoap_namespaces()
    {
        return $this->soap_namespaces;
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
        $xml = '<merc:getVetDocumentChangesListRequest>'.PHP_EOL.
    '<merc:localTransactionId>' . $this->localTransactionId . '</merc:localTransactionId>'.PHP_EOL;
        if (isset($this->initiator))
            $xml .= $this->initiator->getXML();

        /*$xml .= '<base:listOptions>'.PHP_EOL.
            '<base:count>10</base:count>'.PHP_EOL.
            '<base:offset>0</base:offset>'.PHP_EOL.
        '</base:listOptions>'.PHP_EOL.*/

        $xml .= '<base:updateDateInterval>
                     <base:beginDate>'.(\Yii::$app->formatter->asDate($this->date_start, 'yyyy-MM-dd').'T'.\Yii::$app->formatter->asTime($this->date_start, 'HH:mm:ss')).'</base:beginDate>
                     <base:endDate>'.(\Yii::$app->formatter->asDate($this->date_end, 'yyyy-MM-dd').'T'.\Yii::$app->formatter->asTime($this->date_end, 'HH:mm:ss')).'</base:endDate>
                  </base:updateDateInterval>';

        $xml .= '<ent:enterpriseGuid>'.$this->enterpriseGuid.'</ent:enterpriseGuid>'.PHP_EOL.
        '</merc:getVetDocumentChangesListRequest>';

        return $xml;
    }
}