<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\base\Model;

class getVetDocumentByUUIDRequest extends Model
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

    public function getDocumentByUUID($UUID, $raw = false)
    {
        $cache = \Yii::$app->cache;
        $this->UUID = $UUID;

        $doc = mercuryApi::getInstance()->getVetDocumentByUUID($UUID);

        if($raw) {
            return $doc;
        }

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

        $consignee_business = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->consignee->businessEntity->uuid);
        $consignee_enterprise = cerberApi::getInstance()->getEnterpriseByUuid($doc->certifiedConsignment->consignee->enterprise->uuid);

        $enterprise = $consignee_enterprise->enterprise;
        $businessEntity = $consignee_business->businessEntity;

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

        if(isset($doc->certifiedConsignment->batch->origin->owner)) {
            $owner_raw = cerberApi::getInstance()->getBusinessEntityByUuid($doc->certifiedConsignment->batch->origin->uuid);
            $owner = $owner_raw->businessEntity;
        }

        $product_raw = productApi::getInstance()->getProductByGuid($doc->certifiedConsignment->batch->product->guid);
        $product = $product_raw->product->name;

        $sub_product_raw = productApi::getInstance()->getSubProductByGuid($doc->certifiedConsignment->batch->subProduct->guid);

        $sub_product = $sub_product_raw->subProduct->name;

        $unit = dictsApi::getInstance()->getUnitByGuid($doc->certifiedConsignment->batch->unit->guid);

        $country_raw = ikarApi::getInstance()->getCountryByGuid($doc->certifiedConsignment->batch->origin->country->guid);

        $country = $country_raw->country->name;

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
                'value' => MercVsd::$product_types[$doc->certifiedConsignment->batch->productType],
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
                'value' => $doc->certifiedConsignment->batch->volume." ".$unit->unit->name,
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
                'value' => MercVsd::getDate($doc->certifiedConsignment->batch->dateOfProduction),
            ],
            [
                'label' => 'Дата окончания срока годности продукции',
                'value' => MercVsd::getDate($doc->certifiedConsignment->batch->expiryDate),
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
                'value' =>  (isset($owner)) ? ($owner->name.', ИНН:'.$owner->inn) : "-",
            ],
        ];
        $this->purpose = [
            'label' => 'Цель. Назначение груза',
            'value' => $purpose,
        ];

        $this->transportInfo = isset ($doc->certifiedConsignment->transportInfo) ? ([
            'type' => MercVsd::$transport_types[$doc->certifiedConsignment->transportInfo->transportType],
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

        if($doc->referencedDocument->type == 1) {
            $this->waybillSeries = $doc->referencedDocument->issueSeries;
            $this->waybillNumber = $doc->referencedDocument->issueNumber;
            $this->waybillDate = $doc->referencedDocument->issueDate;
        }

        $this->cargoExpertized = isset($doc->authentication->cargoExpertized) ? $doc->authentication->cargoExpertized : null;
        $this->expertiseInfo = $doc->authentication->cargoInspected;

        $this->confirmedBy = [
            ['label' => 'ФИО',
                'value' => $doc->statusChange->specifiedPerson->fio],
            ['label' => 'Должность',
                'value' => $doc->statusChange->specifiedPerson->post]
        ];
        $this->locationProsperity = $doc->authentication->locationProsperity;
        $this->specialMarks = isset($doc->authentication->specialMarks) ? $doc->authentication->specialMarks : null;

        $cache->add('vetDoc_'.$UUID, $this->attributes, 60*5);

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