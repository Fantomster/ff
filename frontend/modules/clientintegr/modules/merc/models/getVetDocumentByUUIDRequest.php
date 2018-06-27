<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

use frontend\modules\clientintegr\modules\merc\helpers\api\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\product\productApi;
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

        $doc = mercApi::getInstance()->getVetDocumentByUUID($UUID);
         //var_dump($raw_doc);
          //  $doc = $raw_doc->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentByUuidResponse->ns2vetDocument;

        if($raw) {
            return $doc;
        }

        $this->issueSeries = (isset($doc->ns2issueSeries)) ? $doc->ns2issueSeries->__toString() : null;
        $this->issueNumber = (isset($doc->ns2issueNumber)) ? $doc->ns2issueNumber->__toString() : null;
        $this->issueDate = $doc->ns2issueDate->__toString();
        $this->form = $doc->ns2form->__toString();
        $this->type = $doc->ns2type->__toString();
        $this->status = $doc->ns2status->__toString();

        $consingtor_buisness = cerberApi::getInstance()->getBusinessEntityByUuid($doc->ns2consignor->entbusinessEntity->bsuuid->__toString());
        $consingtor_enterprise = cerberApi::getInstance()->getEnterpriseByUuid($doc->ns2consignor->ententerprise->bsuuid->__toString());

        $enterprise = $consingtor_enterprise->soapenvBody->v2getEnterpriseByUuidResponse->dtenterprise;
        $businessEntity = $consingtor_buisness->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;

        $this->consignor = [
            [ 'label' => 'Название предприятия',
              'value' => $enterprise->dtname->__toString().'('.
                  $enterprise->dtaddress->dtaddressView->__toString()
                  .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->dtname->__toString().', ИНН:'.$businessEntity->dtinn->__toString(),
            ]
        ];

        $cconsignee_buisness = cerberApi::getInstance()->getBusinessEntityByUuid($doc->ns2consignee->entbusinessEntity->bsuuid->__toString());
        $consignee_enterprise = cerberApi::getInstance()->getEnterpriseByUuid($doc->ns2consignee->ententerprise->bsuuid->__toString());

        $enterprise = $consignee_enterprise->soapenvBody->v2getEnterpriseByUuidResponse->dtenterprise;
        $businessEntity = $cconsignee_buisness->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;

        $this->consignee = [
            [ 'label' => 'Название предприятия',
                'value' => $enterprise->dtname->__toString().'('.
                    $enterprise->dtaddress->dtaddressView->__toString()
                    .')',
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => $businessEntity->dtname->__toString().', ИНН:'.$businessEntity->dtinn->__toString(),
            ]
        ];

        if(isset($doc->ns2broker)) {
            $broker_raw = cerberApi::getInstance()->getBusinessEntityByUuid($doc->ns2broker->bsuuid->__toString());
            $broker = $broker_raw->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;
            $this->broker = ['label' => 'Сведения о фирме-посреднике (перевозчике продукции)',
                'value' => $broker->dtname->__toString() . ', ИНН:' . $broker->dtinn->__toString(),
            ];
        }

        $owner_raw = cerberApi::getInstance()->getBusinessEntityByUuid($doc->ns2batch->ns2owner->bsuuid->__toString());


        $owner = $owner_raw->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity;

        $product_raw = productApi::getInstance()->getProductByGuid($doc->ns2batch->ns2product->bsguid->__toString());
        $product = $product_raw->product->name;

        $sub_product_raw = productApi::getInstance()->getSubProductByGuid($doc->ns2batch->ns2subProduct->bsguid->__toString());

        $sub_product = $sub_product_raw->subProduct->name;

        $unit = dictsApi::getInstance()->getUnitByGuid($doc->ns2batch->ns2unit->bsguid);

        $country_raw = ikarApi::getInstance()->getCountryByGuid($doc->ns2batch->ns2countryOfOrigin->bsguid->__toString());

        $country = $country_raw->country->fullName;

        $purpose = dictsApi::getInstance()->getPurposeByGuid($doc->ns2purpose->bsguid->__toString());
        $purpose = $purpose->purpose->name;

        $producer = null;

        if(isset($doc->ns2batch->ns2producerList->entproducer)) {
            $producer_raw = cerberApi::getInstance()->getEnterpriseByUuid($doc->ns2batch->ns2producerList->entproducer->ententerprise->bsuuid->__toString());

            if(isset($producer_raw->soapBody))
                $producer = $producer_raw->soapBody->v2getEnterpriseByUuidResponse->dtenterprise;
            else
                $producer = $producer_raw->soapenvBody->v2getEnterpriseByUuidResponse->dtenterprise;
            $producer = $producer->dtname->__toString() . '(' .
                $producer->dtaddress->dtaddressView->__toString()
                . ')';
        }

        $this->batch =
        [
            [
                'label' => 'Тип продукции',
                'value' => $this->product_types[$doc->ns2batch->ns2productType->__toString()],
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
                'value' => isset($doc->ns2batch->ns2productItem->prodname) ? $doc->ns2batch->ns2productItem->prodname->__toString() : null,
            ],
            [
                'label' => 'Объем',
                'value' => $doc->ns2batch->ns2volume." ".$unit->unit->mname,
            ],
            [
                'label' => 'Список видов упаковки, которые используются для производственной партии',
                'value' => isset($doc->ns2batch->ns2packingList) ? $doc->ns2batch->ns2packingList->argcpackingForm->argcname->__toString() : null,
            ],
            [
                'label' => 'Общее количество единиц упаковки для производственной партии',
                'value' => isset($doc->ns2batch->ns2packingAmount) ? $doc->ns2batch->ns2packingAmount->__toString() : null,
            ],
            [
                'label' => 'Дата выработки продукции',
                'value' => $this->getDate($doc->ns2batch->ns2dateOfProduction),
            ],
            [
                'label' => 'Дата окончания срока годности продукции',
                'value' => isset($doc->ns2batch->ns2expiryDate) ? $this->getDate($doc->ns2batch->ns2expiryDate) : null,
            ],
            [
                'label' => 'Описывает, является ли продукция скоропортящейся',
                'value' => isset($doc->ns2batch->ns2perishable) ? (($doc->ns2batch->ns2perishable->__toString() == 'true') ? 'Да' : 'Нет') : null,
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
                'value' => isset($doc->ns2batch->ns2productMarkingList) ? $doc->ns2batch->ns2productMarkingList->ns2productMarking->__toString() : null,
            ],
            [
                'label' => 'Является ли продукция некачественной',
                'value' => ($doc->ns2batch->ns2lowGradeCargo->__toString() == 'true') ? 'Да' : 'Нет',
            ],
            [
                'label' => 'Собственник продукции',
                'value' =>  $owner->dtname->__toString().', ИНН:'.$owner->dtinn->__toString(),
            ],
        ];
        $this->purpose = [
            'label' => 'Цель. Назначение груза',
            'value' => $purpose,
        ];

        $this->transportInfo = isset ($doc->ns2transportInfo) ? ([
            'type' => $this->transport_types[$doc->ns2transportInfo->shptransportType->__toString()],
            'numbers' => [
                [
                'label' => 'Номер контейнера (при автомобильной перевозке)',
                'number' => isset($doc->ns2transportInfo->shptransportNumber->shpcontainerNumber) ? $doc->ns2transportInfo->shptransportNumber->shpcontainerNumber->__toString() : null,
                ],
                [
                    'label' => 'Номер вагона',
                    'number' => isset ($doc->ns2transportInfo->shptransportNumber->shpwagonNumber) ? $doc->ns2transportInfo->shptransportNumber->shpwagonNumber->__toString() : null,
                ],
                [
                    'label' => 'Номер автомобиля',
                    'number' => isset($doc->ns2transportInfo->shptransportNumber->shpvehicleNumber) ? $doc->ns2transportInfo->shptransportNumber->shpvehicleNumber->__toString() : null,
                ],
                [
                    'label' => 'Номер прицепа (полуприцепа)',
                    'number' => isset($doc->ns2transportInfo->shptransportNumber->shptrailerNumber) ? $doc->ns2transportInfo->shptransportNumber->shptrailerNumber->__toString() : null,
                ],
                [
                    'label' => 'Название судна (или номер контейнера)',
                    'number' => isset($doc->ns2transportInfo->shptransportNumber->shpshipName) ? $doc->ns2transportInfo->shptransportNumber->shpshipName->__toString() : null,
                ],
                [
                    'label' => 'Номер авиарейса',
                    'number' => isset($doc->ns2transportInfo->shptransportNumber->shpflightNumber) ? $doc->ns2transportInfo->shptransportNumber->shpflightNumber->__toString() : null,
                ]
            ]
        ]) : null;
        $this->transportStorageType = isset($doc->ns2transportStorageType) ? $doc->ns2transportStorageType->__toString() : null;
        $this->cargoReloadingPointList = isset($this->cargoReloadingPointList) ? $this->cargoReloadingPointList->__toString() : null;
        $this->waybillSeries = isset($doc->ns2waybillSeries) ? $doc->ns2waybillSeries->__toString() : null;
        $this->waybillNumber = isset($doc->ns2waybillNumber) ? $doc->ns2waybillNumber->__toString() : null;
        $this->waybillDate = isset($doc->ns2waybillDate) ? $doc->ns2waybillDate->__toString() : null;
        $this->cargoExpertized = isset($doc->ns2cargoExpertized) ? $doc->ns2cargoExpertized->__toString() : null;
        $this->expertiseInfo = $doc->ns2expertiseInfo->__toString();
        $this->confirmedBy = [
            ['label' => 'ФИО',
                'value' => $doc->ns2confirmedBy->argcfio->__toString()],
            ['label' => 'Должность',
                'value' => $doc->ns2confirmedBy->argcpost->__toString()]
        ];
        $this->locationProsperity = $doc->ns2locationProsperity->__toString();
        $this->specialMarks = isset($doc->ns2specialMarks) ? $doc->ns2specialMarks->__toString() : null;

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