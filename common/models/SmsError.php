<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_error".
 *
 * @property integer $id
 * @property string $date
 * @property string $message
 * @property string $target
 * @property string $error
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
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['message', 'target', 'error'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'message' => 'Message',
            'target' => 'Target',
            'error' => 'Error',
        ];
    }
}
