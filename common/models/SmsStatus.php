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
            'text' => Yii::t('app', 'Статус'),
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
