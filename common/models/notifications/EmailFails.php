<?php

namespace common\models\notifications;

use Yii;

/**
 * This is the model class for table "email_fails".
 *
 * @property integer $id
 * @property integer $type
 * @property string $email
 * @property string $body
 *
 */
class EmailFails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email_fails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'body'], 'required'],
            [['body'], 'string'],
            [['email'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'email' => Yii::t('app', 'Email'),
            'body' => Yii::t('app', 'Body'),
        ];
    }
}
