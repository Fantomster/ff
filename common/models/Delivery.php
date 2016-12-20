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
 * @property boolean $mon
 * @property boolean $tue
 * @property boolean $wed
 * @property boolean $thu
 * @property boolean $fri
 * @property boolean $sat
 * @property boolean $sun
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
            [['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'boolean'],
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
            'mon' => 'Пн',
            'tue' => 'Вт',
            'wed' => 'Ср',
            'thu' => 'Чт',
            'fri' => 'Пт',
            'sat' => 'Сб',
            'sun' => 'Вс',
            'min_order_price' => 'Минимальная стоимость заказа',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getDaysString() {
        $days = [];
        if ($this->mon) {
            $days[] = 'Пн';
        }
        if ($this->tue) {
            $days[] = 'Вт'; 
        }
        if ($this->wed) {
            $days[] = 'Ср';
        }
        if ($this->thu) {
            $days[] = 'Чт';
        }
        if ($this->fri) {
            $days[] = 'Пт';
        }
        if ($this->sat) {
            $daysp[] = 'Сб';
        }
        if ($this->sun) {
            $days[] = 'Вс';
        }
        return implode(", ", $days);
    }
}
