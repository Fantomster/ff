<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace api_web\modules\integration\modules\vetis\api\mercury;

use api\common\models\merc\MercVsd;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;
use api_web\modules\integration\modules\vetis\api\dicts\dictsApi;
use api_web\modules\integration\modules\vetis\api\ikar\ikarApi;
use api_web\modules\integration\modules\vetis\api\products\productApi;
use yii\base\Model;

/**
 * Class getVetDocumentByUUID
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class getVetDocumentByUUID extends Model
{
    /**
     * @var
     */
    public $UUID;
    /**
     * @var
     */
    public $issueSeries;
    /**
     * @var
     */
    public $issueNumber;
    /**
     * @var
     */
    public $issueDate;
    /**
     * @var
     */
    public $form;
    /**
     * @var
     */
    public $type;
    /**
     * @var
     */
    public $status;
    /**
     * @var
     */
    public $consignor;
    /**
     * @var
     */
    public $consignee;
    /**
     * @var
     */
    public $batch;
    /**
     * @var
     */
    public $purpose;
    /**
     * @var
     */
    public $broker;
    /**
     * @var
     */
    public $transportInfo;
    /**
     * @var
     */
    public $transportStorageType;
    /**
     * @var
     */
    public $cargoReloadingPointList;
    /**
     * @var
     */
    public $waybillSeries;
    /**
     * @var
     */
    public $waybillNumber;
    /**
     * @var
     */
    public $waybillDate;
    /**
     * @var
     */
    public $cargoExpertized;
    /**
     * @var
     */
    public $expertiseInfo;
    /**
     * @var
     */
    public $confirmedBy;
    /**
     * @var
     */
    public $locationProsperity;
    /**
     * @var
     */
    public $specialMarks;
    /**
     * @var
     */
    public $laboratory_research;
    /**
     * @var
     */
    public $localTransactionId;
    /**
     * @var
     */
    public $vetDocumentType;
    /**
     * @var
     */
    public $vetDocumentStatus;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['UUID', 'issueSeries',
                'issueNumber', 'issueDate', 'form', 'type', 'status', 'consignor',
                'consignee', 'batch', 'purpose', 'broker', 'transportInfo',
                'transportStorageType', 'cargoReloadingPointList', 'waybillSeries',
                'waybillNumber', 'waybillDate', 'cargoExpertized', 'expertiseInfo',
                'confirmedBy', 'locationProsperity', 'specialMarks', 'public $laboratory_research'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UUID'                    => 'Идентификатор ВСД',
            'issueSeries'             => 'Серия ВСД',
            'issueNumber'             => 'Номер ВСД',
            'issueDate'               => 'Дата оформления ВСД',
            'form'                    => 'Форма ВСД',
            'type'                    => 'Тип ВСД',
            'status'                  => 'Статус ВСД',
            'consignor'               => 'Сведения об отправителе продукции',
            'consignee'               => 'Сведения о получателе продукции',
            'batch'                   => 'Сведения о партии продукции',
            'purpose'                 => 'Цель. Назначение груза',
            'broker'                  => 'Сведения о фирме-посреднике (перевозчике продукции)',
            'transportInfo'           => 'Сведения о транспорте',
            'transportStorageType'    => 'Способ хранения продукции при перевозке',
            'cargoReloadingPointList' => 'Сведения о маршруте следования (пунктах перегрузки)',
            'waybillSeries'           => 'Серия товарно-транспортной накладной',
            'waybillNumber'           => 'Номер товарно-транспортной накладной',
            'waybillDate'             => 'Дата товарно-транспортной накладной',
            'cargoExpertized'         => 'Проводилась ли ветсанэкспертиза',
            'expertiseInfo'           => 'Результаты лабораторных исследований',
            'confirmedBy'             => 'Государственный ветврач, подписавший ВСД',
            'locationProsperity'      => 'Благополучие местности',
            'specialMarks'            => 'Особые отметки',
        ];
    }

    /**
     * @param      $UUID
     * @param bool $raw
     * @return mixed
     */
    public function getDocumentByUUID($UUID, $raw = false)
    {
        $this->UUID = $UUID;

        $doc = MercVsd::findOne(['uuid' => $UUID]);

        if ($raw) {
            return unserialize($doc->raw_data);
        }

        $this->issueNumber = (isset($doc->number)) ? $doc->number : null;
        $this->issueDate = $doc->date_doc;
        $this->form = $doc->form;
        $this->type = $doc->type;
        $this->status = $doc->status;

        $hc = cerberApi::getInstance()->getEnterpriseByGuid($doc->sender_guid);
        if (isset($hc)) {
            if (isset($hc->owner)) {
                $hc = cerberApi::getInstance()->getBusinessEntityByGuid($hc->owner->guid);
            }
        }

        $this->consignor = [
            ['label' => 'Название предприятия',
             'value' => isset($doc->sender_name) ? $doc->sender_name : null,
            ],
            ['label' => 'Хозяйствующий субъект (владелец продукции):',
             'value' => isset($hc) ? $hc->name . ', ИНН:' . $hc->inn : null,
            ]
        ];

        $hc = cerberApi::getInstance()->getEnterpriseByGuid($doc->recipient_guid);
        if (isset($hc)) {
            if (isset($hc->owner)) {
                $hc = cerberApi::getInstance()->getBusinessEntityByGuid($hc->owner->guid);
            }
        }

        $this->consignee = [
            ['label' => 'Название предприятия',
             'value' => isset($doc->recipient_name) ? $doc->recipient_name : null,
            ],
            ['label' => 'Хозяйствующий субъект (владелец продукции):',
             'value' => isset($hc) ? $hc->name . ', ИНН:' . $hc->inn : null,
            ]
        ];

        /*if(isset($doc->certifiedConsignment->broker)) {
            $broker_raw = cerberApi::getInstance(Yii::$app->user->identity->organization_id)->getBusinessEntityByUuid($doc->certifiedConsignment->broker->uuid);
            $broker = $broker_raw;
            $this->broker = ['label' => 'Сведения о фирме-посреднике (перевозчике продукции)',
                'value' => $broker->name . ', ИНН:' . $broker->inn,
            ];
        }*/

        if (isset($doc->owner_guid)) {
            $owner = cerberApi::getInstance(\Yii::$app->user->identity->organization_id)->getBusinessEntityByUuid($doc->owner_guid);
        }

        $product_raw = productApi::getInstance(\Yii::$app->user->identity->organization_id)->getProductByGuid($doc->product_guid);
        $product = isset($product_raw) ? $product_raw->name : null;

        $sub_product_raw = productApi::getInstance(\Yii::$app->user->identity->organization_id)->getSubProductByGuid($doc->sub_product_guid);

        $sub_product = isset($sub_product_raw) ? $sub_product_raw->name : null;

        $unit = dictsApi::getInstance(\Yii::$app->user->identity->organization_id)->getUnitByGuid($doc->unit_guid);

        $country_raw = ikarApi::getInstance(\Yii::$app->user->identity->organization_id)->getCountryByGuid($doc->origin_country_guid);

        $country = isset($country_raw) ? $country_raw->name : null;

        /*$purpose = dictsApi::getInstance(Yii::$app->user->identity->organization_id)->getPurposeByGuid($doc->authentication->purpose->guid);
        $purpose = isset($purpose) ? $purpose->name : null;*/

        /*$producer = isset($doc->certifiedConsignment->batch->origin->producer) ? MercVsd::getProduccerData($doc->certifiedConsignment->batch->origin->producer, Yii::$app->user->identity->organization_id) : null;

        if(isset($producer)) {
            $producer = implode(", ",$producer['name']);
        }*/

        $this->batch =
            [
                [
                    'label' => 'Тип продукции',
                    'value' => MercVsd::$product_types[$doc->product_type],
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
                    'value' => isset($doc->product_name) ? $doc->product_name : null,
                ],
                [
                    'label' => 'Объем',
                    'value' => $doc->amount . " " . (isset($unit) ? $unit->name : ''),
                ],
                /*[
                    'label' => 'Список видов упаковки, которые используются для производственной партии',
                    'value' => isset($doc->certifiedConsignment->batch->packingList) ? $doc->certifiedConsignment->batch->packingList->packingForm->name : null,

                ],
                [
                    'label' => 'Общее количество единиц упаковки для производственной партии',
                    'value' => isset($doc->certifiedConsignment->batch->packingAmount) ? $doc->certifiedConsignment->batch->packingAmount : null,
                ],*/
                [
                    'label' => 'Дата выработки продукции',
                    'value' => $doc->production_date,
                ],
                [
                    'label' => 'Дата окончания срока годности продукции',
                    'value' => $doc->expiry_date,
                ],
                [
                    'label' => 'Описывает, является ли продукция скоропортящейся',
                    'value' => isset($doc->perishable) ? (($doc->perishable == 'true') ? 'Да' : 'Нет') : null,
                ],
                [
                    'label' => 'Страна происхождения продукции',
                    'value' => $country,
                ],
                [
                    'label' => 'Список производителей продукции',
                    'value' => $doc->producer_name,
                ],
                /*[
                    'label' => 'Список маркировки, доступный для данного производителя',
                    'value' => isset($doc->certifiedConsignment->batch->productMarkingList) ? $doc->certifiedConsignment->batch->productMarkingList->productMarking : null,
                ],*/
                [
                    'label' => 'Является ли продукция некачественной',
                    'value' => ($doc->low_grade_cargo == 'true') ? 'Да' : 'Нет',
                ],
                [
                    'label' => 'Собственник продукции',
                    'value' => (isset($owner)) ? ($owner->name . ', ИНН:' . $owner->inn) : "-",
                ],
            ];
        /*$this->purpose = [
            'label' => 'Цель. Назначение груза',
            'value' => $purpose,
        ];*/

        $transportInfo = json_decode($doc->transport_info, true);
        $this->transportInfo = (isset ($transport_info) && isset($transportInfo['transportType'])) ? ([
            'type'    => MercVsd::$transport_types[$transportInfo['transportType']],
            'numbers' => [
                [
                    'label'  => 'Номер контейнера (при автомобильной перевозке)',
                    'number' => isset($transportInfo['transportNumber']['containerNumber']) ? $transportInfo['transportNumber']['containerNumber'] : null,
                ],
                [
                    'label'  => 'Номер вагона',
                    'number' => isset ($transportInfo['transportNumber']['agonNumber']) ? $transportInfo['transportNumber']['wagonNumber'] : null,
                ],
                [
                    'label'  => 'Номер автомобиля',
                    'number' => isset($transportInfo['transportNumber']['vehicleNumber']) ? $transportInfo['transportNumber']['vehicleNumber'] : null,
                ],
                [
                    'label'  => 'Номер прицепа (полуприцепа)',
                    'number' => isset($transportInfo['transportNumber']['trailerNumber']) ? $transportInfo['transportNumber']['trailerNumber'] : null,
                ],
                [
                    'label'  => 'Название судна (или номер контейнера)',
                    'number' => isset($transportInfo['transportNumber']['shipName']) ? $transportInfo['transportNumber']['shipName'] : null,
                ],
                [
                    'label'  => 'Номер авиарейса',
                    'number' => isset($transportInfo['transportNumber']['flightNumber']) ? $transportInfo['transportNumber']['flightNumber'] : null,
                ]
            ]
        ]) : null;
        $this->transportStorageType = $doc->transport_storage_type;
        //$this->cargoReloadingPointList = isset($doc->certifiedConsignment->cargoReloadingPointList) ? $doc->certifiedConsignment->cargoReloadingPointList : null;

        $this->waybillSeries = $doc->waybill_number;
        $this->waybillDate = $doc->waybill_date;

        /*if(isset($doc->referencedDocument)) {
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
        }*/

        $other = json_decode($doc->other_info, true);

        $this->cargoExpertized = $other['cargoExpertized'];
        $this->locationProsperity = $other['locationProsperity'];
        $this->specialMarks = $other['specialMarks'];

        $confirmed_by = json_decode($doc->confirmed_by, true);

        $this->confirmedBy = [
            ['label' => 'ФИО',
             'value' => isset($confirmed_by['fio']) ? $confirmed_by['fio'] : "-"],
            ['label' => 'Должность',
             'value' => isset($confirmed_by['post']) ? $confirmed_by['post'] : ""]
        ];

        $laboratory_research = [json_decode($doc->laboratory_research, true)];
        foreach ($laboratory_research as $item) {
            if (isset($item['operator']) && isset($item['expertiseID']) && isset($item['actualDateTime']) && isset($item['conclusion'])) {
                $this->laboratory_research = [
                    $item['operator']['name'] . " эксп №" . $item['expertiseID'] . " от " . date("Y-m-d h:i:s", strtotime($item['actualDateTime'])) . " ( " . $item['conclusion'] . " )"
                ];
            }
        }
    }

    /**
     * @return null|string
     */
    public function getWaybillNumber()
    {
        if (empty($this->waybillNumber) && empty($this->waybillSeries)) {
            return null;
        }

        $res = '';
        if (isset($this->waybillSeries)) {
            $res = $this->waybillSeries . ' ';
        }

        if (isset($this->waybillNumber)) {
            $res .= $this->waybillNumber;
        }

        return $res;
    }
}
