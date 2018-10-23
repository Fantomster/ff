<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "waybill".
 *
 * @property int $id
 * @property int $acquirer_id
 * @property int $status_id
 * @property int $service_id
 * @property string $outer_number_code
 * @property string $outer_number_additional
 * @property string $outer_store_uuid
 * @property string $outer_duedate
 * @property string $outer_note
 * @property string $outer_order_date
 * @property string $outer_contractor_uuid
 * @property int $vat_included
 *
 * @property string $doc_date
 * @property int $is_duedate
 * @property string $created_at
 * @property string $updated_at
 * @property string $exported_at
 * @property int $payment_delay
 * @property string $payment_delay_date
 *
 *
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
            [['outer_duedate', 'doc_date', 'created_at', 'updated_at', 'exported_at', 'payment_delay_date'], 'safe'],
            [['outer_number_code', 'outer_number_additional', 'outer_note', 'outer_order_date'], 'string', 'max' => 45],
            [['outer_store_uuid', 'outer_contractor_uuid'], 'string', 'max' => 36],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'acquirer_id' => 'Acquirer ID',
            'status_id' => 'Bill Status ID',
            'service_id' => 'Service ID',
            'outer_number_code' => 'Outer Number Code',
            'outer_number_additional' => 'Outer Number Additional',
            'outer_store_uuid' => 'Outer Store Uuid',
            'outer_duedate' => 'Outer Duedate',
            'outer_note' => 'Outer Note',
            'outer_order_date' => 'Outer Order Date',
            'outer_contractor_uuid' => 'Outer Contractor Uuid',
            'vat_included' => 'Vat Included'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillContents()
    {
        return $this->hasMany(WaybillContent::class, ['waybill_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function getIsMercuryCert()
    {
        return (WaybillContent::find()->where(['waybill_id' => $this->id])->andWhere('merc_uuid is not null')->count()) > 0;
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
        return WaybillContent::find()->where(['waybill_id' => $this->id])->sum('sum_with_vat');
    }

}
