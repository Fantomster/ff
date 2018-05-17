<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

use frontend\modules\clientintegr\modules\merc\helpers\mercApi;

class getVetDocumentByUUIDRequest extends BaseRequest
{
    public $UUID;
    public $issueSeries;
    public $issueNumber;
    public $issueDate;
    public $form;
    public $type;
    public $status;
    public $consignor;
    public $consignee;
    public $batch;
    public $purpose;
    public $broker;
    public $transportInfo;
    public $transportStorageType;
    public $cargoReloadingPointList;
    public $waybillSeries;
    public $waybillNumber;
    public $waybillDate;
    public $cargoExpertized;
    public $expertiseInfo;
    public $confirmedBy;
    public $locationProsperity;
    public $specialMarks;


    public $localTransactionId;
    public $vetDocumentType;
    public $vetDocumentStatus;

    private $initiator;
    private $enterpriseGuid;
    private $soap_namespaces = ['xmlns:merc="http://api.vetrf.ru/schema/cdm/mercury/applications"', ' xmlns:base="http://api.vetrf.ru/schema/cdm/base"'];

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

    /*public function rules()
    {
        return [
            [['localTransactionId', 'vetDocumentType', 'vetDocumentStatus', 'enterpriseGuid', 'initiator', '_soap_namespace'], 'safe'],
        ];
    }*/

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UUID' => 'Идентификатор ВСД',
            'issueSeries' => 'Серия ВСД',
            'issueNumber' => 'Номер ВСД',
            'issueDate' => 'Дата оформления ВСД',
            'form' => 'Форма ВСД',
            'type' => 'Тип ВСД',
            'status' => 'Статус ВСД',
            'consignor' => 'Сведения об отправителе продукции',
            'consignee' => 'Сведения о получателе продукции',
            'batch' => 'Сведения о партии продукции',
            'purpose' => 'Цель. Назначение груза',
            'broker' => 'Сведения о фирме-посреднике (перевозчике продукции)',
            'transportInfo' => 'Сведения о транспорте',
            'transportStorageType' => 'Способ хранения продукции при перевозке',
            'cargoReloadingPointList' => 'Сведения о маршруте следования (пунктах перегрузки)',
            'waybillSeries' => 'Серия товарно-транспортной накладной',
            'waybillNumber' => 'Номер товарно-транспортной накладной',
            'waybillDate' => 'Дата товарно-транспортной накладной',
            'cargoExpertized' => 'Проводилась ли ветсанэкспертиза',
            'expertiseInfo' => 'Результаты лабораторных исследований',
            'confirmedBy' => 'Государственный ветврач, подписавший ВСД',
            'locationProsperity' => 'Благополучие местности',
            'specialMarks' => 'Особые отметки',
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
        $xml = '<merc:getVetDocumentByUuidRequest>'.PHP_EOL.
    '<merc:localTransactionId>' . $this->localTransactionId . '</merc:localTransactionId>'.PHP_EOL;
        if (isset($this->initiator))
            $xml .= $this->initiator->getXML();

       /* $xml .= '<base:listOptions>'.PHP_EOL.
            '<base:count>10</base:count>'.PHP_EOL.
            '<base:offset>0</base:offset>'.PHP_EOL.
        '</base:listOptions>'.PHP_EOL.
        '<vet:vetDocumentType>INCOMING</vet:vetDocumentType>'.PHP_EOL.
        '<vet:vetDocumentStatus>UTILIZED</vet:vetDocumentStatus>'.PHP_EOL;*/
        $xml .= '<base:uuid>'.$this->UUID.'</base:uuid>';
        $xml .= '<ent:enterpriseGuid>'.$this->enterpriseGuid.'</ent:enterpriseGuid>'.PHP_EOL.
        '</merc:getVetDocumentByUuidRequest>';

        return $xml;
    }

    public function getDocumentByUUID($UUID)
    {
        $this->UUID = $UUID;
        $raw_doc = mercApi::getInstance()->getVetDocumentByUUID($UUID);
        var_dump($raw_doc);

        $doc = $raw_doc->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentByUuidResponse->ns2vetDocument;

        $this->issueSeries = $doc->ns2issueSeries;
        $this->issueNumber = $doc->ns2issueNumber;
        $this->issueDate = $doc->ns2issueDate;
        $this->form = $doc->ns2from;
        $this->type = $doc->ns2type;
        $this->status = $doc->ns2status;

        $consingtor_buisness = mercApi::getInstance()->getBusinessEntityByUuid($doc->ns2consignor->entbusinessEntity->bsuuid->__toString());
        $consingtor_enterprise = mercApi::getInstance()->getEnterpriseByUuid($doc->ns2consignor->ententerprise->bsuuid->__toString());

        $enterprise = $consingtor_enterprise->soapenvBody->v2getEnterpriseByUuidResponse->dtenterprise;
        $businessEntity = $consingtor_buisness->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;

        $this->consignor = [
            [ 'label' => 'Название предприятия',
              'value' => $enterprise->dtname->__toString().'('.
                  $enterprise->dtaddress->dtaddressView->__toString()
                  .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->dtname->__toString().', ИНН:'.$businessEntity->dtin->__toString(),
            ]
        ];

        $cconsignee_buisness = mercApi::getInstance()->getBusinessEntityByUuid($doc->ns2consignee->entbusinessEntity->bsuuid->__toString());
        $consignee_enterprise = mercApi::getInstance()->getEnterpriseByUuid($doc->ns2consignee->ententerprise->bsuuid->__toString());

        $enterprise = $consignee_enterprise->soapenvBody->v2getEnterpriseByUuidResponse->dtenterprise;
        $businessEntity = $cconsignee_buisness->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;

        $this->consignee = [
            [ 'label' => 'Название предприятия',
                'value' => $enterprise->dtname->__toString().'('.
                    $enterprise->dtaddress->dtaddressView->__toString()
                    .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->dtname->__toString().', ИНН:'.$businessEntity->dtin->__toString(),
            ]
        ];

        $this->batch;
        $this->purpose;
        $this->broker;
        $this->transportInfo;
        $this->transportStorageType = $doc->ns2transportStorageType;
        $this->cargoReloadingPointList;
        $this->waybillSeries = $doc->ns2waybillSeries;
        $this->waybillNumber = $doc->ns2waybillNumber;
        $this->waybillDate = $doc->ns2waybillDate;
        $this->cargoExpertized = $doc->ns2cargoExpertized;
        $this->expertiseInfo = $doc->ns2expertiseInfo;
        $this->confirmedBy = [
            ['label' => 'ФИО',
                'value' => $doc->ns2confirmedBy->argcfio],
            ['label' => 'Должность',
                'value' => $doc->ns2confirmedBy->argcpost]
        ];
        $this->locationProsperity = $doc->ns2locationProsperity;
        $this->specialMarks = $doc->ns2specialMarks;
    }
}