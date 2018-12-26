<?php

namespace common\models;

/**
 * This is the model class for table "edi_order".
 *
 * @property int    $id             Идентификатор записи в таблице
 * @property int    $order_id       Идентификатор заказа
 * @property string $invoice_number Номер счёта-фактуры, связанного с заказом
 * @property string $invoice_date   Дата счёта-фактуры, связанного с заказом
 * @property string $lang           Двухбуквенное обозначение языка, на котором сделан заказ
 *
 * @property Order  $order
 */
class EdiOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%edi_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['order_id'], 'integer'],
            [['order_id'], 'unique'],
            [['lang', 'invoice_number', 'invoice_date'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
