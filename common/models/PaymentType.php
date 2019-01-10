<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_type".
 *
 * @property int              $type_id    Идентификатор записи в таблице
 * @property string           $title      Наименование типа платных услуг
 * @property string           $created_at Дата и время создания записи в таблице
 * @property string           $updated_at Дата и время последнего изменения записи в таблице
 *
 * @property BillingPayment[] $billingPayments
 * @property PaymentTarif[]   $paymentTarifs
 */
class PaymentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function () {
                    return date("Y-m-d H:i:s");
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $title = \Yii::t('app', 'app.models.PaymentType.type_payment');

        if ($title == 'app.models.PaymentType.type_payment') {
            $title = Yii::t('app', 'Тип платежа');
        }

        return [
            'type_id'    => 'Type ID',
            'title'      => $title,
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillingPayments()
    {
        return $this->hasMany(BillingPayment::className(), ['payment_type_id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentTarifs()
    {
        return $this->hasMany(PaymentTarif::className(), ['payment_type_id' => 'type_id']);
    }
}
