<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property int          $payment_id      Идентификатор записи в таблице
 * @property double       $total           Сумма оплаты
 * @property string       $receipt_number  Номер выставленного счёта
 * @property int          $organization_id Идентификатор организации, которой выставлен счёт за оплату услуги
 * @property int          $type_payment    Идентификатор типа платной услуги
 * @property string       $email           Е-мэйл организации, оплачивающей услугу
 * @property string       $phone           Номер телефона организации, оплачивающей платную услугу
 * @property string       $date            Дата оплаты
 * @property string       $created_at      Дата и время создания записи в таблице
 * @property string       $updated_at      Дата и время последнего изменения записи в таблице
 * @property int          $status          Показатель статуса счёта за услугу (0 - ошибочный, 1 - выставленный, 2 -
 *           оплаченный)
 *
 * @property Organization $organization
 * @property PaymentType  $payment
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total', 'organization_id', 'type_payment', 'date'], 'required'],
            [['total'], 'number'],
            [['receipt_number', 'email', 'phone'], 'string'],
            [['organization_id', 'type_payment', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['type_payment'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentType::className(), 'targetAttribute' => ['type_payment' => 'type_id']],
            [['organization_id', 'type_payment'], 'compare', 'compareValue' => 0, 'operator' => '!=', 'message' => 'Необходимо выбрать из списка'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'payment_id'      => 'ID',
            'date'            => Yii::t('app', 'Дата оплаты'),
            'total'           => Yii::t('app', 'Сумма'),
            'receipt_number'  => Yii::t('app', 'Номер выставленного счета'),
            'organization_id' => Yii::t('app', 'Организация'),
            'type_payment'    => Yii::t('app', 'Тип оплаты'),
            'email'           => 'Email',
            'phone'           => 'Phone',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
            'status'          => 'status',
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
            $this->date = date('Y-m-d H:i:s', strtotime($this->date));
        }

        return parent::beforeSave($insert);
    }
}
