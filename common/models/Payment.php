<?php

namespace common\models;

use Yii;
use yii\helpers\FormatConverter;

/**
 * This is the model class for table "payment".
 *
 * @property integer $payment_id
 * @property double $total
 * @property string $receipt_number
 * @property integer $organization_id
 * @property integer $type_payment
 * @property string $email
 * @property string $phone
 * @property string $date
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $organization
 * @property PaymentType $payment
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['total', 'organization_id', 'type_payment', 'date'], 'required'],
            [['total'], 'number'],
            [['receipt_number', 'email', 'phone'], 'string'],
            [['organization_id', 'type_payment'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentType::className(), 'targetAttribute' => ['payment_id' => 'type_id']],
            [['organization_id', 'type_payment'], 'compare', 'compareValue' => 0, 'operator' => '!=', 'message' => 'Необходимо выбрать из списка'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'payment_id' => 'ID',
            'date' => Yii::t('app', 'Дата оплаты'),
            'total' => Yii::t('app', 'Сумма'),
            'receipt_number' => Yii::t('app', 'Номер выставленного счета'),
            'organization_id' => Yii::t('app', 'Организация'),
            'type_payment' => Yii::t('app', 'Тип оплаты'),
            'email' => 'Email',
            'phone' => 'Phone',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(PaymentType::className(), ['type_id' => 'type_payment']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (isset($this->date) && !empty($this->date)) {
            $this->date = date('Y-m-d', strtotime($this->date));
        }

        return parent::beforeSave($insert);
    }
}
