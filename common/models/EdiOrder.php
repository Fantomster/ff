<?php

namespace common\models;

/**
 * This is the model class for table "edi_order".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $lang
 * @property string $invoice_number
 * @property string $invoice_date
 */
class EdiOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edi_order';
    }


    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [];
    }


    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
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
