<?php

namespace frontend\modules\billing\models;

/**
 * This is the model class for table "billing_payment".
 *
 * @property integer $billing_payment_id
 * @property double $amount
 * @property integer $currency_id
 * @property integer $user_id
 * @property integer $organization_id
 * @property integer $status
 * @property integer $payment_type_id
 * @property string $idempotency_key
 * @property string $created_at
 * @property string $capture_at
 * @property string $payment_at
 * @property string $refund_at
 * @property string $external_payment_id
 * @property string $external_created_at
 * @property string $external_expires_at
 * @property string $provider
 *
 * @property Currency $currency
 * @property Organization $organization
 * @property PaymentType $paymentType
 * @property User $user
 */

use common\models\Payment;
use common\models\User;
use yii\db\ActiveRecord;
use common\models\Currency;
use common\models\PaymentType;
use common\models\Organization;
use frontend\modules\billing\providers\ProviderInterface;
use yii\db\Expression;

class BillingPayment extends ActiveRecord
{
    CONST STATUS_NEW = 0;
    CONST STATUS_WAIT = 1;
    CONST STATUS_SUCCESS = 2;
    CONST STATUS_REFUND = 9;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'billing_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'payment_type_id'], 'required'],
            [['amount'], 'number'],
            [['currency_id', 'user_id', 'organization_id', 'status', 'payment_type_id'], 'integer'],
            [['created_at', 'capture_at', 'payment_at', 'refund_at', 'external_created_at', 'external_expires_at', 'provider'], 'safe'],
            [['idempotency_key'], 'string', 'max' => 36],
            [['external_payment_id'], 'string', 'max' => 50],
            [['provider'], 'string', 'max' => 255],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['payment_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentType::className(), 'targetAttribute' => ['payment_type_id' => 'type_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'billing_payment_id' => 'Billing Payment ID',
            'amount' => 'Сумма оплаты',
            'currency_id' => 'Валюта',
            'user_id' => 'Пользователь',
            'organization_id' => 'Организация',
            'status' => 'Статус платежа',
            'payment_type_id' => 'Тип платежа',
            'idempotency_key' => 'Ключ идемпотенции',
            'created_at' => 'Дата создания платежа',
            'capture_at' => 'Дата подверждения',
            'payment_at' => 'Дата оплаты',
            'refund_at' => 'Дата отмены',
            'external_payment_id' => 'Ключ платежа в платежной системе',
            'external_created_at' => 'Дата создания платежа у провайдера',
            'external_expires_at' => 'Срок для подтверждения платежа',
            'provider' => 'provider'
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        //Обработаем сумму как нужно, перед созданием
        //чтобы не писать всякий шлак
        $this->setAmount($this->amount);
        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \Exception
     */
    public function beforeSave($insert)
    {
        //Ключ идемпотенции устанавливаем один раз при создании
        if (empty($this->idempotency_key)) {
            $this->idempotency_key = static::generateIdemKey();
        }

        if (!empty($this->billing_payment_id)) {
            $receipt_id = 'mc_' . $this->billing_payment_id;
            $model = Payment::find()->where(['receipt_number' => $receipt_id])->one();
            if (empty($model)) {
                $model = new Payment();
                $model->date = date('d.m.Y H:i:s');
            }

            $model->setAttributes([
                'organization_id' => $this->organization_id,
                'receipt_number' => $receipt_id,
                'total' => $this->amount,
                'type_payment' => $this->payment_type_id,
                'status' => $this->status
            ]);

            if (!$model->validate() || !$model->save()) {
                throw new \Exception(array_keys($model->getFirstErrors())[0] . ':' . array_pop($model->getFirstErrors()));
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
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
    public function getPaymentType()
    {
        return $this->hasOne(PaymentType::className(), ['type_id' => 'payment_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param string $iso_code
     */
    public function setCurrency($iso_code = 'RUB')
    {
        $currency = Currency::findOne(['iso_code' => $iso_code]);
        if ($currency) {
            $this->currency_id = $currency->id;
        }
    }

    /**
     * @param $amount
     * @throws \Exception
     */
    public function setAmount($amount)
    {
        $amount_r = preg_replace('#[^0-9\.]#', '', str_replace(',', '.', trim($amount)));
        if (is_numeric($amount_r) && $amount_r >= 0) {
            $this->amount = round($amount_r, 2);
        } else {
            throw new \Exception('Not validate Amount :' . $amount);
        }
    }

    /**
     * @param $provider
     * @throws \Exception
     */
    public function setProvider($provider)
    {
        if ($provider instanceof ProviderInterface) {
            $this->provider = get_class($provider);
        } else {
            throw new \Exception('setProvider($provider) not instance ProviderInterface');
        }
    }

    /**
     * @param $provider
     * @throws \Exception
     */
    public function checkProvider($provider)
    {
        if ($provider instanceof ProviderInterface) {
            if ($this->provider !== get_class($provider)) {
                throw new \Exception('При проведении операции с платежом, провайдер операции и платежа должны совпадать.');
            }
        } else {
            throw new \Exception('checkProvider($provider) not instance ProviderInterface');
        }
    }

    /**
     * @return string
     */
    public static function generateIdemKey()
    {
        return uniqid(time() . '_', true);
    }
}
