<?php

namespace common\models;

use api_web\behaviors\WaybillBehavior;
use api_web\components\Registry;
use common\helpers\DBNameHelper;
use Yii;
use yii\behaviors\AttributesBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "waybill".
 *
 * @property int              $id
 * @property int              $acquirer_id
 * @property int              $status_id
 * @property int              $service_id
 * @property string           $outer_number_code
 * @property string           $outer_number_additional
 * @property string           $outer_store_id
 * @property string           $outer_duedate
 * @property string           $outer_note
 * @property string           $outer_order_date
 * @property string           $outer_agent_id
 * @property int              $vat_included
 * @property string           $doc_date
 * @property int              $is_duedate
 * @property string           $created_at
 * @property string           $updated_at
 * @property string           $exported_at
 * @property int              $payment_delay
 * @property string           $payment_delay_date
 * @property Order            $order
 * @property OuterStore       $outerStore
 * @property OuterAgent       $outerAgent
 * @property bool             $readyToExport
 * @property string           $edi_invoice
 * @property string           $outer_document_id
 * @property WaybillContent[] $waybillContents
 */
class Waybill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'waybill';
    }

    /**
     * @throws \Exception
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => WaybillBehavior::class,
                'model' => $this
            ],
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acquirer_id', 'service_id'], 'required'],
            [['acquirer_id', 'status_id', 'service_id', 'vat_included', 'is_duedate', 'payment_delay'], 'integer'],
            [['outer_duedate', 'doc_date', 'created_at', 'updated_at', 'exported_at', 'payment_delay_date', 'outer_document_id'], 'safe'],
            [['outer_number_code', 'outer_number_additional', 'outer_note', 'outer_order_date'], 'string', 'max' => 45],
            [['outer_store_id', 'outer_agent_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                      => 'ID',
            'acquirer_id'             => 'Acquirer ID',
            'status_id'               => 'Bill Status ID',
            'service_id'              => 'Service ID',
            'outer_number_code'       => 'Outer Number Code',
            'outer_number_additional' => 'Outer Number Additional',
            'outer_store_id'          => 'Outer Store Id',
            'outer_duedate'           => 'Outer Duedate',
            'outer_note'              => 'Outer Note',
            'outer_order_date'        => 'Outer Order Date',
            'outer_agent_id'          => 'Outer Agent Id',
            'vat_included'            => 'Vat Included',
            'outer_document_id'       => 'Outer Document Id'
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->getOldAttribute('status_id') != Registry::WAYBILL_UNLOADED) {
                if ($this->getAttribute('status_id') == Registry::WAYBILL_UNLOADED) {
                    $this->exported_at = gmdate("Y-m-d H:i:s");
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillContents()
    {
        return $this->hasMany(WaybillContent::class, ['waybill_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterStore()
    {
        return $this->hasOne(OuterStore::class, ['id' => 'outer_store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterAgent()
    {
        return $this->hasOne(OuterAgent::class, ['id' => 'outer_agent_id']);
    }

    /**
     * @return bool
     */
    public function getIsMercuryCert()
    {
        $count = WaybillContent::find()
            ->where(['waybill_id' => $this->id])
            ->leftJoin(DBNameHelper::getMainName() . '.' . OrderContent::tableName() . ' as oc', 'oc.id = order_content_id')
            ->andWhere('oc.merc_uuid is not null')
            ->count();

        return ($count) > 0;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return WaybillContent::find()->where(['waybill_id' => $this->id])->count();
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return round(WaybillContent::find()->where(['waybill_id' => $this->id])->sum('sum_with_vat'), 2);
    }

    /**
     * @return float
     */
    public function getTotalPriceWithOutVat()
    {
        return round(WaybillContent::find()->where(['waybill_id' => $this->id])->sum('sum_without_vat'), 2);
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        $wcModel = WaybillContent::find()->where('waybill_id = :wid AND order_content_id is not null', [':wid' => $this->id])->one();
        if (!empty($wcModel)) {
            return $wcModel->orderContent->order;
        }
        return null;
    }

    /**
     * Проверка, готова ли запись накладной к выгрузке
     *
     * @return bool
     */
    public function getReadyToExport()
    {
        //Атрибуты, обязательные для заполнения при выгрузке
        $requireAttributes = [
            'outer_agent_id',
            'outer_store_id',
            'outer_number_code',
            'outer_number_additional'
        ];
        //Проверяем их в текущей моделе
        foreach ($requireAttributes as $attribute) {
            $value = $this->getAttribute($attribute);
            //Если хоть какое то значение не задано, возвращаем false
            if (is_null($value) or empty($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Смена статуса накладной в "Сопоставлена"
     *
     * @return bool
     */
    public function changeStatusToCompared()
    {
        //Если нет агента в накладной, нечего там проверять
        if ($this->readyToExport === false) {
            //Если накладная была сопоставлена, но стала не готова к выгрузке
            //Меняем статус на сформирована
            if ($this->status_id == Registry::WAYBILL_COMPARED) {
                $this->changeStatusToFormed();
            }
            return true;
        }
        //Если в накладной нет позиций, никогда не переведем ее в статус "Сопоставлена"
        $contents = $this->waybillContents;
        if (empty($contents)) {
            return true;
        }
        /** @var \common\models\WaybillContent $waybillContent */
        //Проверяем все позиции накладной, что они готовы к выгрузке
        //Если хоть одна не готова, статус не меняем
        $waybillContentCompared = true;
        foreach ($contents as $waybillContent) {
            if ($waybillContent->readyToExport === false) {
                $waybillContentCompared = false;
                break;
            }
        }

        if ($waybillContentCompared === false) {
            if ($this->status_id == Registry::WAYBILL_COMPARED) {
                $this->changeStatusToFormed();
            }
            return true;
        }
        //Если дошли сюда
        //И накладная в статусе "Cформирована" или "Сброшена"
        if (in_array($this->status_id, [Registry::WAYBILL_FORMED, Registry::WAYBILL_RESET])) {
            //то ставим статус накладной "Сопоставлена"
            $this->status_id = Registry::WAYBILL_COMPARED;
            return $this->save(false);
        }
    }

    /**
     * @return bool
     */
    private function changeStatusToFormed()
    {
        $this->status_id = Registry::WAYBILL_FORMED;
        return $this->save(false);
    }
}
