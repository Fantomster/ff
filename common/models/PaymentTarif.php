<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "payment_tarif".
 *
 * @property int              $tarif_id             Идентификатор записи в таблице
 * @property int              $payment_type_id      Идентификатор типа платных услуг
 * @property int              $organization_type_id Идентификатор категории организации
 * @property double           $price                Стоимость услуги
 * @property int              $status               Статус платной услуги (0 - не активна, 1- активна)
 * @property int              $organization_id      Идентификатор организации, которой услуга предоставляется
 * @property int              $individual           Показатель статуса индивидуальности платной услуги (0 - не
 *           индивидуальная, 1 - индивидуальная)
 * @property string           $created_at           Дата и время создания записи в таблице
 * @property string           $updated_at           Дата и время последнего изменения записи в таблице
 *
 * @property Organization     $organization
 * @property OrganizationType $organizationType
 * @property PaymentType      $paymentType
 */
class PaymentTarif extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_tarif}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_type_id', 'organization_type_id', 'price'], 'required'],
            [['payment_type_id', 'organization_type_id', 'status', 'organization_id', 'individual'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['organization_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['organization_type_id' => 'id']],
            [['payment_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentType::className(), 'targetAttribute' => ['payment_type_id' => 'type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tarif_id'             => Yii::t('app', 'Tarif ID'),
            'payment_type_id'      => Yii::t('app', 'Payment Type ID'),
            'organization_type_id' => Yii::t('app', 'Organization Type ID'),
            'price'                => Yii::t('app', 'Price'),
            'status'               => Yii::t('app', 'Status'),
            'organization_id'      => Yii::t('app', 'Организация'),
            'individual'           => Yii::t('app', 'Индивидуальный прайс'),
            'created_at'           => Yii::t('app', 'Created At'),
            'updated_at'           => Yii::t('app', 'Updated At'),
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
    public function getOrganizationType()
    {
        return $this->hasOne(OrganizationType::className(), ['id' => 'organization_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentType()
    {
        return $this->hasOne(PaymentType::className(), ['type_id' => 'payment_type_id']);
    }
}
