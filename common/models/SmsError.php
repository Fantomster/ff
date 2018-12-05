<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_error".
 *
 * @property integer $id
 * @property string $date
 * @property string $error
 * @property integer $error_code
 * @property integer $send_sms_id
 */
class SmsError extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_error';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['date']
                ],
                'value'      => function ($event) {
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
            [['date'], 'safe'],
            [['error_code', 'sms_send_id'], 'integer'],
            [['message', 'target', 'error'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'date'        => 'Date',
            'error'       => 'Error',
            'error_code'  => 'Error code',
            'sms_send_id' => 'Sms ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(SmsSend::className(), ['id' => 'sms_send_id']);
    }

}
