<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_send".
 *
 * @property integer $id
 * @property string $sms_id
 * @property integer $status
 * @property string $text
 * @property string $target
 * @property string $send_date
 * @property string $status_date
 * @property string $provider
 *
 * @property SmsStatus $status0
 */
class SmsSend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_send';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['text'], 'string'],
            [['send_date', 'status_date'], 'safe'],
            [['sms_id', 'target', 'provider'], 'string', 'max' => 255],
            [['status'], 'exist', 'skipOnError' => true, 'targetClass' => SmsStatus::className(), 'targetAttribute' => ['status' => 'status']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sms_id' => 'Sms ID',
            'status' => 'Status',
            'text' => 'Text',
            'target' => 'Target',
            'send_date' => 'Send Date',
            'status_date' => 'Status Date',
            'provider' => 'Provider',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus0()
    {
        return $this->hasOne(SmsStatus::className(), ['status' => 'status']);
    }
}
