<?php

namespace common\models;

use api\common\models\merc\MercVsd;
use Yii;

/**
 * This is the model class for table "waybill_content".
 *
 * @property int $id
 * @property int $waybill_id
 * @property int $order_content_id
 * @property int $product_outer_id
 * @property double $quantity_waybill
 * @property double $vat_waybill
 * @property string $merc_uuid
 * @property int $unload_status
 * @property int $sum_with_vat
 * @property int $sum_without_vat
 * @property int $price_with_vat
 * @property int $price_without_vat
 *
 *
 * @property Waybill $waybill
 */
class WaybillContent extends yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'waybill_content';
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
            [['waybill_id'], 'required'],
            [['waybill_id', 'order_content_id', 'product_outer_id', 'unload_status', 'sum_with_vat', 'sum_without_vat', 'price_with_vat', 'price_without_vat', 'outer_unit_id'], 'integer'],
            [['quantity_waybill', 'vat_waybill', 'koef'], 'number'],
            [['merc_uuid'], 'string', 'max' => 255],
            [['waybill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Waybill::className(), 'targetAttribute' => ['waybill_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'waybill_id' => 'Waybill ID',
            'order_content_id' => 'Order Content ID',
            'product_outer_id' => 'Product Outer ID',
            'quantity_waybill' => 'Quantity Waybill',
            'vat_waybill' => 'Vat Waybill',
            'merc_uuid' => 'Merc Uuid',
            'unload_status' => 'Unload Status',
            'koef' => 'Коэффициент',
            'outer_unit_id' => 'ID единицы измерения'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybill()
    {
        return $this->hasOne(Waybill::className(), ['id' => 'waybill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * */
    public function getMercVsd()
    {
        return $this->hasOne(MercVsd::className(), ['uuid' => 'merc_uuid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderContent()
    {
        return $this->hasOne(OrderContent::className(), ['id' => 'order_content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductOuter()
    {
        return $this->hasOne(OuterProduct::className(), ['id' => 'product_outer_id']);
    }
}
