<?php

namespace common\models;

use api\common\models\merc\MercVsd;
use api_web\behaviors\WaybillContentBehavior;
use common\helpers\DBNameHelper;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "waybill_content".
 *
 * @property int          $id
 * @property int          $waybill_id
 * @property int          $order_content_id
 * @property int          $outer_product_id
 * @property double       $quantity_waybill
 * @property double       $vat_waybill
 * @property string       $merc_uuid
 * @property int          $sum_with_vat
 * @property int          $sum_without_vat
 * @property int          $price_with_vat
 * @property int          $price_without_vat
 * @property int          $outer_unit_id
 * @property int          $koef
 * @property bool         $readyToExport
 * @property OrderContent $orderContent
 * @property OuterProduct $productOuter
 * @property Waybill      $waybill
 */
class WaybillContent extends \yii\db\ActiveRecord
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
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => WaybillContentBehavior::class,
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
            [['waybill_id'], 'required'],
            [['waybill_id', 'order_content_id', 'outer_product_id', 'outer_unit_id'], 'integer'],
            [['sum_with_vat', 'sum_without_vat', 'price_with_vat', 'price_without_vat', 'quantity_waybill', 'vat_waybill', 'koef'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['waybill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Waybill::className(), 'targetAttribute' => ['waybill_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                       => 'ID',
            'waybill_id'               => 'ID Накладной',
            'order_content_id'         => 'ID Позиции заказа',
            'outer_product_id'         => 'ID Продукта',
            'quantity_waybill'         => 'Количество',
            'vat_waybill'              => 'НДС',
            'koef'                     => 'Коэффициент',
            'outer_unit_id'            => 'ID Единицы измерения'
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
        return $this->hasOne(MercVsd::className(), ['uuid' => 'merc_uuid'])->via('orderContent');
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
        return $this->hasOne(OuterProduct::className(), ['id' => 'outer_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterUnit()
    {
        return $this->hasOne(OuterUnit::className(), ['id' => 'outer_unit_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        //Получаем атрибуты которые изменились
        $dirtyAttr = $this->getDirtyAttributes();
        //Если прилетело изменение сумма без НДС
        //Нужно вычислить цену
        if (isset($dirtyAttr['sum_without_vat'])) {
            //Будем считать цену только тогда, если нам ее не прислали
            //Если же прислали, пересчитаем суммы ниже
            if (!isset($dirtyAttr['price_without_vat'])) {
                $this->setAttribute('price_without_vat', round($this->sum_without_vat / $this->quantity_waybill, 2));
                $dirtyAttr['price_without_vat'] = $this->getAttribute('price_without_vat');
            }
        }
        //Если изменилась цена или количество, пересчитываем суммы
        if (isset($dirtyAttr['price_without_vat']) || isset($dirtyAttr['quantity_waybill']) || isset($dirtyAttr['vat_waybill'])) {
            if (isset($dirtyAttr['price_without_vat']) || isset($dirtyAttr['vat_waybill'])) {
                $value = $this->price_without_vat * ((100 + ($this->vat_waybill ?? 0)) / 100);
                $this->setAttribute('price_with_vat', $value);
            }
            //Пересчет сумм позиции
            $this->refreshSum();
            //Если присутствует связь с orderContent
            //обновляем все записи waybill_content, которые привязаны к этому же order_content_id
            if (!empty($this->orderContent)) {
                self::updateAll(
                    [
                        'price_without_vat' => $this->getAttribute('price_without_vat'),
                        'quantity_waybill'  => $this->getAttribute('quantity_waybill'),
                        'sum_with_vat'      => $this->getAttribute('sum_with_vat'),
                        'sum_without_vat'   => $this->getAttribute('sum_without_vat'),
                        'vat_waybill'       => $this->getAttribute('vat_waybill')
                    ],
                    'order_content_id = :oid AND id != :id',
                    [
                        ':oid' => $this->orderContent->id,
                        ":id"  => $this->id
                    ]
                );
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * Обновление сумм
     */
    public function refreshSum()
    {
        $this->setAttribute('sum_with_vat', ($this->quantity_waybill * $this->price_with_vat));
        $this->setAttribute('sum_without_vat', ($this->quantity_waybill * $this->price_without_vat));
        /**
         * Необходимо чтобы запустить пересчет заказа
         */
        $this->setOldAttribute('sum_with_vat', 0);
        $this->setOldAttribute('sum_without_vat', 0);
        /**
         * Необходимо чтобы запустить пересчет заказа
         */
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
            'outer_product_id',
            'quantity_waybill',
            'vat_waybill',
            'price_without_vat',
            'price_with_vat',
            'sum_with_vat',
            'sum_without_vat',
            'koef',
            'outer_unit_id'
        ];
        //Проверяем их в текущей моделе
        foreach ($requireAttributes as $attribute) {
            $value = $this->getAttribute($attribute);
            //Если хоть какое то значение не задано, возвращаем false
            if (is_null($value)) {
                return false;
            }
            //Коэфициент не должен быть 0
            if ($attribute == 'koef' && $value == 0) {
                return false;
            }
        }
        return true;
    }
}
