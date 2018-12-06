<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_status".
 *
 * @property integer $id
 * @property integer $status
 * @property string $text
 *
 * @property SmsSend[] $smsSends
 */
class SmsStatus extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;
    const STATUS_DELIVERED = 2;
    const STATUS_TIMEOUT = 3;
    const STATUS_FAILED = 5;
    const STATUS_REJECTED = 8;
    const STATUS_CANCELED = 20;
    const STATUS_SYSTEM_ERROR = 21;
    const STATUS_UNKNOWN = 22;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['text'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status ID',
            'text' => Yii::t('app', 'common.models.sms_status.status', ['ru'=>'Статус']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsSends()
    {
        return $this->hasMany(SmsSend::className(), ['status' => 'status']);
    }
}
