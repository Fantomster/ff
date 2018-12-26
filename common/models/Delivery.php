<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "delivery".
 *
 * @property int    $id                       Идентификатор записи в таблице
 * @property int    $vendor_id                Идентификатор организации-поставщика
 * @property string $delivery_charge          Стоимость доставки
 * @property string $min_free_delivery_charge Минимальная стоимость заказа, при которой доставка осуществляется
 *           бесплатно
 * @property int    $mon                      Показатель возможности доставки заказа в понедельник (0 - нет доставки, 1
 *           - доставка возможна)
 * @property int    $tue                      Показатель возможности доставки заказа во вторник (0 - нет доставки, 1 -
 *           доставка возможна)
 * @property int    $wed                      Показатель возможности доставки заказа в среду (0 - нет доставки, 1 -
 *           доставка возможна)
 * @property int    $thu                      Показатель возможности доставки заказа в четверг (0 - нет доставки, 1 -
 *           доставка возможна)
 * @property int    $fri                      Показатель возможности доставки заказа в пятницу (0 - нет доставки, 1 -
 *           доставка возможна)
 * @property int    $sat                      Показатель возможности доставки заказа в субботу (0 - нет доставки, 1 -
 *           доставка возможна)
 * @property int    $sun                      Показатель возможности доставки заказа в воскресенье (0 - нет доставки, 1
 *           - доставка возможна)
 * @property string $min_order_price          Минимальная стоимость заказа
 * @property string $created_at               Дата и время создания записи в таблице
 * @property string $updated_at               Дата и время последнего изменения записи в таблице
 */
class Delivery extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                       => 'ID',
            'vendor_id'                => Yii::t('app', 'Vendor ID'),
            'delivery_charge'          => Yii::t('app', 'common.models.delivery_price', ['ru' => 'Стоимость доставки']),
            'min_free_delivery_charge' => Yii::t('app', 'common.models.free_delivery', ['ru' => 'Cтоимость заказа для бесплатной доставки']),
            'mon'                      => Yii::t('app', 'common.models.mon', ['ru' => 'Пн']),
            'tue'                      => Yii::t('app', 'common.models.tue', ['ru' => 'Вт']),
            'wed'                      => Yii::t('app', 'common.models.wed', ['ru' => 'Ср']),
            'thu'                      => Yii::t('app', 'common.models.thu', ['ru' => 'Чт']),
            'fri'                      => Yii::t('app', 'common.models.fri', ['ru' => 'Пт']),
            'sat'                      => Yii::t('app', 'common.models.sat', ['ru' => 'Сб']),
            'sun'                      => Yii::t('app', 'common.models.sun', ['ru' => 'Вс']),
            'min_order_price'          => Yii::t('app', 'common.models.min_price', ['ru' => 'Минимальная стоимость заказа']),
            'created_at'               => 'Created At',
            'updated_at'               => 'Updated At',
        ];
    }

    /**
     * @return string
     */
    public function getDaysString()
    {
        $days = [];
        if ($this->mon) {
            $days[] = Yii::t('app', 'common.models.mon_two', ['ru' => 'Пн']);
        }
        if ($this->tue) {
            $days[] = Yii::t('app', 'common.models.tue_two', ['ru' => 'Вт']);
        }
        if ($this->wed) {
            $days[] = Yii::t('app', 'common.models.wed_two', ['ru' => 'Ср']);
        }
        if ($this->thu) {
            $days[] = Yii::t('app', 'common.models.thu_two', ['ru' => 'Чт']);
        }
        if ($this->fri) {
            $days[] = Yii::t('app', 'common.models.fri_two', ['ru' => 'Пт']);
        }
        if ($this->sat) {
            $days[] = Yii::t('app', 'common.models.sat_two', ['ru' => 'Сб']);
        }
        if ($this->sun) {
            $days[] = Yii::t('app', 'common.models.sun_two', ['ru' => 'Вс']);
        }
        return implode(", ", $days);
    }
}
