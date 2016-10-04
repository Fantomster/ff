<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "delivery".
 *
 * @property integer $id
 * @property integer $vendor_id
 * @property string $delivery_charge
 * @property string $min_free_delivery_charge
 * @property boolean $delivery_mon
 * @property boolean $delivery_tue
 * @property boolean $delivery_wed
 * @property boolean $delivery_thu
 * @property boolean $delivery_fri
 * @property boolean $delivery_sat
 * @property boolean $delivery_sun
 * @property string $min_order_price
 * @property string $created_at
 * @property string $updated_at
 */
class Delivery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_id'], 'required'],
            [['vendor_id'], 'integer'],
            [['delivery_mon', 'delivery_tue', 'delivery_wed', 'delivery_thu', 'delivery_fri', 'delivery_sat', 'delivery_sun'], 'boolean'],
            [['delivery_charge', 'min_free_delivery_charge', 'min_order_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'delivery_charge' => 'Стоимость доставки',
            'min_free_delivery_charge' => 'Cтоимость заказа для бесплатной доставки',
            'delivery_mon' => 'Пнд',
            'delivery_tue' => 'Втр',
            'delivery_wed' => 'Срд',
            'delivery_thu' => 'Чтв',
            'delivery_fri' => 'Птн',
            'delivery_sat' => 'Сбт',
            'delivery_sun' => 'Вск',
            'min_order_price' => 'Минимальная стоимость заказа',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
