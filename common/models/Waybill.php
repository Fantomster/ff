<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "waybill".
 *
 * @property int $id
 * @property int $acquirer_id
 * @property int $bill_status_id
 * @property int $readytoexport
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acquirer_id', 'service_id'], 'required'],
            [['acquirer_id', 'bill_status_id', 'readytoexport', 'service_id', 'vat_included'], 'integer'],
            [['outer_duedate'], 'safe'],
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
            'bill_status_id' => 'Bill Status ID',
            'readytoexport' => 'Readytoexport',
            'service_id' => 'Service ID',
            'outer_number_code' => 'Outer Number Code',
            'outer_number_additional' => 'Outer Number Additional',
            'outer_store_uuid' => 'Outer Store Uuid',
            'outer_duedate' => 'Outer Duedate',
            'outer_note' => 'Outer Note',
            'outer_order_date' => 'Outer Order Date',
            'outer_contractor_uuid' => 'Outer Contractor Uuid',
            'vat_included' => 'Vat Included',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillContents()
    {
        return $this->hasMany(WaybillContent::className(), ['waybill_id' => 'id']);
    }
}
