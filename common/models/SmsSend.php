<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%sms_send}}".
 *
 * @property integer $id
 * @property string $sms_id
 * @property integer $status_id
 * @property string $text
 * @property string $target
 * @property string $created_at
 * @property string $updated_at
 * @property string $provider
 * @property integer $order_id
 *
 * @property SmsStatus $status
 */
class SmsSend extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_send}}';
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
            [['status_id', 'order_id'], 'integer'],
            [['text'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['sms_id', 'target', 'provider'], 'string', 'max' => 255],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => SmsStatus::className(), 'targetAttribute' => ['status_id' => 'status']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'sms_id'     => 'Sms ID',
            'status_id'  => 'Status ID',
            'order_id'   => 'Order ID',
            'text'       => Yii::t('app', 'common.models.message', ['ru' => 'Сообщение']),
            'target'     => Yii::t('app', 'common.models.reciever', ['ru' => 'Получатель']),
            'created_at' => Yii::t('app', 'common.models.send_date', ['ru' => 'Дата отправки']),
            'updated_at' => Yii::t('app', 'common.models.cancel_date', ['ru' => 'Дата смены статуса']),
            'provider'   => 'Provider',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(SmsStatus::className(), ['status' => 'status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

}
