<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;

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

    public $forms = [
        'CERTCU1' => 'Форма 1 ветеринарного сертификата ТС',
        'LIC1' => 'Форма 1 ветеринарного свидетельства',
        'CERTCU2' => 'Форма 2 ветеринарного сертификата ТС',
        'LIC2' => 'Форма 2 ветеринарного свидетельства',
        'CERTCU3' => 'Форма 3 ветеринарного сертификата ТС',
        'LIC3' => 'Форма 3 ветеринарного свидетельства',
        'NOTE4' => 'Форма 4 ветеринарной справки',
        'CERT5I' => 'Форма 5i ветеринарного сертификата',
        'CERT61' => 'Форма 6.1 ветеринарного сертификата',
        'CERT62' => 'Форма 6.2 ветеринарного сертификата',
        'CERT63' => 'Форма 6.3 ветеринарного сертификата',
        'PRODUCTIVE' => 'Производственный сертификат',
    ];

    public $transport_types = [
        1 => 'Автомобильный',
        2 => 'Железнодорожный',
        3 => 'Авиатранспортный',
        4 => 'Морской (контейнер)',
        5 => 'Морской (трюм)',
        6 => 'Речной',
        7 => 'Перегон',
    ];

    public $product_types = [
        1 => 'Мясо и мясопродукты',
        2 => 'Корма и кормовые добавки',
        3 => 'Живые животные',
        4 => 'Лекарственные средства',
        5 => 'Пищевые продукты',
        6 => 'Непищевые продукты и другое',
        7 => 'Рыба и морепродукты',
        8 => 'Продукция, не требующая разрешения',
    ];

    public $storage_types = [
        'FROZEN' => 'Замороженный',
        'CHILLED' => 'Охлажденный',
        'COOLED' => 'Охлаждаемый',
        'VENTILATED' => 'Вентилируемый'
    ];

    public function rules()
    {
        return [
            [['UUID', 'issueSeries',
        'issueNumber', 'issueDate', 'form', 'type', 'status', 'consignor',
        'consignee', 'batch', 'purpose', 'broker', 'transportInfo',
        'transportStorageType', 'cargoReloadingPointList', 'waybillSeries',
        'waybillNumber', 'waybillDate', 'cargoExpertized', 'expertiseInfo',
        'confirmedBy', 'locationProsperity', 'specialMarks'], 'safe'],
        ];
    }

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

    public function getDocumentByUUID($UUID, $raw = false)
    {
        $cache = \Yii::$app->cache;
        $attributes = $cache->get('vetDoc_'.$UUID);
        if($attributes && !$raw) {
            $this->setAttributes($attributes);
                return;
        }

        $this->UUID = $UUID;

        $doc = mercuryApi::getInstance()->getVetDocumentByUUID($UUID);
         //var_dump($raw_doc);
          //  $doc = $raw_doc->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentByUuidResponse->ns2vetDocument;

        if($raw) {
            return $doc;
        }

        /*echo "<pre>";
        var_dump($doc);
        echo"</pre>";
die();*/
        $this->issueSeries = (isset($doc->issueSeries)) ? $doc->nissueSeries : null;
        $this->issueNumber = (isset($doc->issueNumber)) ? $doc->issueNumber : null;
        $this->issueDate = $doc->issueDate;
        $this->form = $doc->vetDForm;
        $this->type = $doc->vetDType;
        $this->status = $doc->vetDStatus;

        $consingtor_buisness = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->consignor->businessEntity->uuid);
        $consingtor_enterprise = cerberApi::getInstance()->getEnterpriseByUuid($doc->certifiedConsignment->consignor->enterprise->uuid);

        $enterprise = $consingtor_enterprise->enterprise;
        $businessEntity = $consingtor_buisness->businessEntity;

        $this->consignor = [
            [ 'label' => 'Название предприятия',
              'value' => $enterprise->name.'('.
                  $enterprise->address->addressView
                  .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->name.', ИНН:'.$businessEntity->inn,
            ]
        ];

        $cconsignee_buisness = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->consignee->businessEntity->uuid);
        $consignee_enterprise = cerberApi::getInstance()->getEnterpriseByUuid($doc->certifiedConsignment->consignee->enterprise->uuid);

        $enterprise = $consignee_enterprise->enterprise;
        $businessEntity = $cconsignee_buisness->businessEntity;

        $this->consignee = [
            [ 'label' => 'Название предприятия',
                'value' => $enterprise->name.'('.
                    $enterprise->address->addressView
                    .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->name.', ИНН:'.$businessEntity->inn,
            ]
        ];

        if(isset($doc->certifiedConsignment->broker)) {
            $broker_raw = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->broker->uuid);
            $broker = $broker_raw->businessEntity;
            $this->broker = ['label' => 'Сведения о фирме-посреднике (перевозчике продукции)',
                'value' => $broker->name . ', ИНН:' . $broker->inn,
            ];
        }

        if(isset($doc->certifiedConsignment->batch->owner)) {
            $owner_raw = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->batch->owner->uuid);
            $owner = $owner_raw->businessEntity;
        }

        $product_raw = productApi::getInstance()->getProductByGuid($doc->certifiedConsignment->batch->product->guid);
        $product = $product_raw->product->name;

        $sub_product_raw = productApi::getInstance()->getSubProductByGuid($doc->certifiedConsignment->batch->subProduct->guid);

        $sub_product = $sub_product_raw->subProduct->name;

        $unit = dictsApi::getInstance()->getUnitByGuid($doc->certifiedConsignment->batch->unit->guid);

        $country_raw = ikarApi::getInstance()->getCountryByGuid($doc->certifiedConsignment->batch->origin->country->guid);

        $country = $country_raw->country->fullName;

        $purpose = dictsApi::getInstance()->getPurposeByGuid($doc->authentication->purpose->guid);
        $purpose = $purpose->purpose->name;

        $producer = null;

        if(isset($doc->certifiedConsignment->batch->producerList->producer)) {
            $producer_raw = cerberApi::getInstance()->getEnterpriseByUuid($doc->certifiedConsignment->batch->producerList->producer->enterprise->uuid);
            $producer = $producer_raw->enterprise;

            $producer = $producer->name . '(' .
                $producer->address->addressView
                . ')';
        }

        $this->batch =
        [
            [
                'label' => 'Тип продукции',
                'value' => $this->product_types[$doc->certifiedConsignment->batch->productType],
            ],
            [
                'label' => 'Продукция',
                'value' => $product,
            ],
            [
                'label' => 'Вид продукции',
                'value' => $sub_product,
            ],
            [
                'label' => 'Наименование произведенной продукции в номенклатуре производителя',
                'value' => isset($doc->certifiedConsignment->batch->productItem->name) ? $doc->certifiedConsignment->batch->productItem->name : null,
            ],
            [
                'label' => 'Объем',
                'value' => $doc->certifiedConsignment->batch->volume." ".$unit->unit->mname,
            ],
            [
                'label' => 'Список видов упаковки, которые используются для производственной партии',
                'value' => isset($doc->certifiedConsignment->batch->packingList) ? $doc->certifiedConsignment->batch->packingList->packingForm->name : null,
            ],
            [
                'label' => 'Общее количество единиц упаковки для производственной партии',
                'value' => isset($doc->certifiedConsignment->batch->packingAmount) ? $doc->certifiedConsignment->batch->packingAmount : null,
            ],
            [
                'label' => 'Дата выработки продукции',
                'value' => $this->getDate($doc->certifiedConsignment->batch->dateOfProduction),
            ],
            [
                'label' => 'Дата окончания срока годности продукции',
                'value' => isset($doc->certifiedConsignment->batch->expiryDate) ? $this->getDate($doc->certifiedConsignment->batch->expiryDate) : null,
            ],
            [
                'label' => 'Описывает, является ли продукция скоропортящейся',
                'value' => isset($doc->certifiedConsignment->batch->perishable) ? (($doc->certifiedConsignment->batch->perishable == 'true') ? 'Да' : 'Нет') : null,
            ],
            [
                'label' => 'Страна происхождения продукции',
                'value' => $country,
            ],
            [
                'label' => 'Список производителей продукции',
                'value' => $producer,
            ],
            [
                'label' => 'Список маркировки, доступный для данного производителя',
                'value' => isset($doc->certifiedConsignment->batch->productMarkingList) ? $doc->certifiedConsignment->batch->productMarkingList->productMarking : null,
            ],
            [
                'label' => 'Является ли продукция некачественной',
                'value' => ($doc->certifiedConsignment->batch->lowGradeCargo == 'true') ? 'Да' : 'Нет',
            ],
            [
                'label' => 'Собственник продукции',
                'value' =>  $owner->name.', ИНН:'.$owner->inn,
            ],
        ];
        $this->purpose = [
            'label' => 'Цель. Назначение груза',
            'value' => $purpose,
        ];

        $this->transportInfo = isset ($doc->certifiedConsignment->transportInfo) ? ([
            'type' => $this->transport_types[$doc->certifiedConsignment->transportInfo->transportType],
            'numbers' => [
                [
                'label' => 'Номер контейнера (при автомобильной перевозке)',
                'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->containerNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->shpcontainerNumber : null,
                ],
                [
                    'label' => 'Номер вагона',
                    'number' => isset ($doc->certifiedConsignment->transportInfo->transportNumber->wagonNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->shpwagonNumber : null,
                ],
                [
                    'label' => 'Номер автомобиля',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->vehicleNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->shpvehicleNumber : null,
                ],
                [
                    'label' => 'Номер прицепа (полуприцепа)',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->trailerNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->shptrailerNumber : null,
                ],
                [
                    'label' => 'Название судна (или номер контейнера)',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->shipName) ? $doc->certifiedConsignment->transportInfo->transportNumber->shpshipName : null,
                ],
                [
                    'label' => 'Номер авиарейса',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->flightNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->shpflightNumber : null,
                ]
            ]
        ]) : null;
        $this->transportStorageType = isset($doc->certifiedConsignment->transportStorageType) ? $doc->certifiedConsignment->transportStorageType : null;
        $this->cargoReloadingPointList = isset($doc->certifiedConsignment->cargoReloadingPointList) ? $doc->certifiedConsignment->cargoReloadingPointList : null;
        $this->waybillSeries = isset($doc->referencedDocumen->issueSeries) ? $doc->referencedDocumen->issueSeries : null;
        $this->waybillNumber = isset($doc->referencedDocumen->issueNumber) ? $doc->referencedDocumen->isueNumber : null;
        $this->waybillDate = isset($doc->referencedDocumen->issueDate) ? $doc->referencedDocumen->issueDate : null;
        $this->cargoExpertized = isset($doc->authentication->cargoExpertized) ? $doc->authentication->cargoExpertized : null;
        $this->expertiseInfo = $doc->authentication->cargoInspected;
        $this->confirmedBy = [
            ['label' => 'ФИО',
                'value' => $doc->authentication->statusChange->specifiedPerson->fio],
            ['label' => 'Должность',
                'value' => $doc->authentication->statusChange->specifiedPerson->post]
        ];
        $this->locationProsperity = $doc->authentication->locationProsperity;
        $this->specialMarks = isset($doc->authentication->specialMarks) ? $doc->authentication->specialMarks : null;

        $cache->add('vetDoc_'.$UUID, $this->attributes, 60*5);

    }

    public function getDate($date_raw)
    {

        if(isset($date_raw->ns2informalDate))
            return $date_raw->ns2informalDate->__toString();


        $first_date = '';
        if(isset($date_raw->ns2firstDate->bsyear))
            $first_date .= $date_raw->ns2firstDate->bsyear;

        if(isset($date_raw->ns2firstDate->bsmonth))
            $first_date .= '-'.$date_raw->ns2firstDate->bsmonth;

        if(isset($date_raw->ns2firstDate->bsday))
            $first_date .= '-'.$date_raw->ns2firstDate->bsday;

        if (isset($date_raw->ns2firstDate->bshour)){
            $first_date .= " ";
            if (strlen($date_raw->ns2firstDate->bshour)==1)
                $first_date .= "0";
            $first_date .= $date_raw->ns2firstDate->bshour.":00:00";
        }

        if($date_raw->ns2secondDate)
        {
            $second_date = '';
            if(isset($date_raw->ns2secondDate->bsyear))
                $second_date .= $date_raw->ns2secondDate->bsyear;

            if(isset($date_raw->ns2firstDate->bsmonth))
                $second_date .= '-'.$date_raw->ns2secondDate->bsmonth;

            if(isset($date_raw->ns2firstDate->bsday))
                $second_date .= '-'.$date_raw->ns2secondDate->bsday;

            if (isset($date_raw->ns2firstDate->bshour)){
                $second_date .= " ";
                if (strlen($date_raw->ns2secondDate->bshour)==1)
                    $second_date .= "0";
                $second_date .= $date_raw->ns2secondDate->bshour.":00:00";
            }

            return 'с '.$first_date.' до '.$second_date;
        }

        return $first_date;
    }

    public function getNumber ()
    {
        if(empty($this->issueNumber) && empty($this->issueSeries))
            return null;

        $res = '';
        if(isset($this->issueSeries))
            $res =  $this->issueSeries.' ';

        if(isset($this->issueNumber))
            $res .=  $this->issueNumber;

        return $res;
    }

    public function getWaybillNumber ()
    {
        if(empty($this->waybillNumber) && empty($this->waybillSeries))
            return null;

        $res = '';
        if(isset($this->waybillSeries))
            $res =  $this->waybillSeries.' ';

        if(isset($this->waybillNumber))
            $res .=  $this->waybillNumber;

        return $res;
    }
}