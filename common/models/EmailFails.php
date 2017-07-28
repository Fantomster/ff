<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_fails".
 *
 * @property integer $id
 * @property integer $email_notification_id
 * @property integer $type
 * @property string $email
 * @property string $body
 *
 * @property EmailNotification $emailNotification
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
            [['email_notification_id', 'email', 'body'], 'required'],
            [['email_notification_id', 'type'], 'integer'],
            [['body'], 'string'],
            [['email'], 'string', 'max' => 255],
            [['email_notification_id'], 'exist', 'skipOnError' => true, 'targetClass' => EmailNotification::className(), 'targetAttribute' => ['email_notification_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email_notification_id' => Yii::t('app', 'Email Notification ID'),
            'type' => Yii::t('app', 'Type'),
            'email' => Yii::t('app', 'Email'),
            'body' => Yii::t('app', 'Body'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailNotification()
    {
        return $this->hasOne(EmailNotification::className(), ['id' => 'email_notification_id']);
    }
}
