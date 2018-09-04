<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\base\Model;
use Yii;

class getVetDocumentByUUID extends Model
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
        $this->UUID = $UUID;

        $doc = mercuryApi::getInstance()->getVetDocumentByUUID($UUID);

        if($raw) {
            return $doc;
        }

        $this->issueSeries = (isset($doc->issueSeries)) ? $doc->issueSeries : null;
        $this->issueNumber = (isset($doc->issueNumber)) ? $doc->issueNumber : null;
        $this->issueDate = $doc->issueDate;
        $this->form = $doc->vetDForm;
        $this->type = $doc->vetDType;
        $this->status = $doc->vetDStatus;

        $consignor_business = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getBusinessEntityByGuid($doc->certifiedConsignment->consignor->businessEntity->guid);
        $consignor_enterprise = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getEnterpriseByGuid($doc->certifiedConsignment->consignor->enterprise->guid);

        $enterprise = $consignor_enterprise;
        $businessEntity = $consignor_business;
        
        $this->consignor = [
            [ 'label' => 'Название предприятия',
              'value' => isset($enterprise) ? $enterprise->name.'('.
                  $enterprise->address->addressView
                  .')': null,
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => isset($businessEntity) ? $businessEntity->name.', ИНН:'.$businessEntity->inn : null,
            ]
        ];

        $consignee_business = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getBusinessEntityByUuid($doc->certifiedConsignment->consignee->businessEntity->uuid);
        $consignee_enterprise = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getEnterpriseByUuid($doc->certifiedConsignment->consignee->enterprise->uuid);

        $enterprise = $consignee_enterprise;
        $businessEntity = $consignee_business;

        $this->consignee = [
            [ 'label' => 'Название предприятия',
                'value' => isset($enterprise) ? $enterprise->name.'('.
                    $enterprise->address->addressView
                    .')' : null,
            ],
            [ 'label' => 'Хозяйствующий субъект (владелец продукции):',
                'value' => isset($businessEntity) ? $businessEntity->name.', ИНН:'.$businessEntity->inn : null,
            ]
        ];

        if(isset($doc->certifiedConsignment->broker)) {
            $broker_raw = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getBusinessEntityByUuid($doc->certifiedConsignment->broker->uuid);
            $broker = $broker_raw;
            $this->broker = ['label' => 'Сведения о фирме-посреднике (перевозчике продукции)',
                'value' => $broker->name . ', ИНН:' . $broker->inn,
            ];
        }

        if(isset($doc->certifiedConsignment->batch->origin->owner)) {
            $owner_raw = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getBusinessEntityByUuid($doc->certifiedConsignment->batch->origin->uuid);
            $owner = $owner_raw->businessEntity;
        }

        $product_raw = productApi::getInstance(Yii::$app->user->identity->organization_id)->getProductByGuid($doc->certifiedConsignment->batch->product->guid);
        $product = isset($product_raw) ? $product_raw->name : null;

        $sub_product_raw = productApi::getInstance(Yii::$app->user->identity->organization_id)->getSubProductByGuid($doc->certifiedConsignment->batch->subProduct->guid);

        $sub_product = isset($sub_product_raw) ? $sub_product_raw->name : null;

        $unit = dictsApi::getInstance(Yii::$app->user->identity->organization_id)->getUnitByGuid($doc->certifiedConsignment->batch->unit->guid);

        $country_raw = ikarApi::getInstance(Yii::$app->user->identity->organization_id)->getCountryByGuid($doc->certifiedConsignment->batch->origin->country->guid);

        $country = isset($country_raw) ? $country_raw->name : null;

        $purpose = dictsApi::getInstance(Yii::$app->user->identity->organization_id)->getPurposeByGuid($doc->authentication->purpose->guid);
        $purpose = isset($purpose) ? $purpose->name : null;

        $producer = isset($doc->certifiedConsignment->batch->origin->producer) ? MercVsd::getProduccerData($doc->certifiedConsignment->batch->origin->producer, Yii::$app->user->identity->organization_id) : null;

        if(isset($producer)) {
            $producer = implode(", ",$producer['name']);
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
                'value' => $doc->certifiedConsignment->batch->volume." ".isset($unit) ? $unit->name : '',
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

        $this->transportInfo = (isset ($doc->certifiedConsignment->transportInfo) && isset($doc->certifiedConsignment->transportInfo->transportType)) ? ([
            'type' => MercVsd::$transport_types[$doc->certifiedConsignment->transportInfo->transportType],
            'numbers' => [
                [
                'label' => 'Номер контейнера (при автомобильной перевозке)',
                'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->containerNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->containerNumber : null,
                ],
                [
                    'label' => 'Номер вагона',
                    'number' => isset ($doc->certifiedConsignment->transportInfo->transportNumber->wagonNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->wagonNumber : null,
                ],
                [
                    'label' => 'Номер автомобиля',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->vehicleNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->vehicleNumber : null,
                ],
                [
                    'label' => 'Номер прицепа (полуприцепа)',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->trailerNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->trailerNumber : null,
                ],
                [
                    'label' => 'Название судна (или номер контейнера)',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->shipName) ? $doc->certifiedConsignment->transportInfo->transportNumber->shipName : null,
                ],
                [
                    'label' => 'Номер авиарейса',
                    'number' => isset($doc->certifiedConsignment->transportInfo->transportNumber->flightNumber) ? $doc->certifiedConsignment->transportInfo->transportNumber->flightNumber : null,
                ]
            ]
        ]) : null;
        $this->transportStorageType = isset($doc->certifiedConsignment->transportStorageType) ? $doc->certifiedConsignment->transportStorageType : null;
        $this->cargoReloadingPointList = isset($doc->certifiedConsignment->cargoReloadingPointList) ? $doc->certifiedConsignment->cargoReloadingPointList : null;

        if(isset($doc->referencedDocument)) {
            $docs = null;
            if (!is_array($doc->referencedDocument))
                $docs[] = $doc->referencedDocument;
            else
                $docs = $doc->referencedDocument;

            foreach ($docs as $item) {
                if (($item->type >= 1) && ($item->type <= 5)) {
                    $this->waybillSeries = isset($item->issueSeries) ? $item->issueSeries : null;
                    $this->waybillNumber = $item->issueNumber;
                    $this->waybillDate = $item->issueDate;
                    break;
                }
            }
        }

        $this->cargoExpertized = isset($doc->authentication->cargoExpertized) ? $doc->authentication->cargoExpertized : null;
        $this->expertiseInfo = $doc->authentication->cargoInspected;

        $this->confirmedBy = [
            ['label' => 'ФИО',
                'value' => isset($doc->statusChange->specifiedPerson->fio) ? $doc->statusChange->specifiedPerson->fio : "-"],
            ['label' => 'Должность',
                'value' => isset($doc->statusChange->specifiedPerson->post) ? $doc->statusChange->specifiedPerson->post : ""]
        ];
        $this->locationProsperity = $doc->authentication->locationProsperity;
        $this->specialMarks = isset($doc->authentication->specialMarks) ? $doc->authentication->specialMarks : null;
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