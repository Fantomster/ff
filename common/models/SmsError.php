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
 * @property string $sms_id
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
            [['error_code'], 'integer'],
            [['message', 'target', 'error', 'sms_id'], 'string', 'max' => 255],
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
            'sms_id' => 'Sms ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSms()
    {
        return $this->hasOne(SmsSend::className(), ['id' => 'sms_id']);
    }

}
