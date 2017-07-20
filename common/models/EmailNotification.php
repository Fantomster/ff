<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $active
 * @property integer $orders
 * @property integer $requests
 * @property integer $changes
 * @property integer $invites
 * @property integer $last_fail
 *
 * @property EmailFails[] $emailFails
 * @property User $user
 */
class EmailNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'active', 'orders', 'requests', 'changes', 'invites', 'last_fail'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'active' => Yii::t('app', 'Active'),
            'orders' => Yii::t('app', 'Orders'),
            'requests' => Yii::t('app', 'Requests'),
            'changes' => Yii::t('app', 'Changes'),
            'invites' => Yii::t('app', 'Invites'),
            'last_fail' => Yii::t('app', 'Last Fail'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailFails()
    {
        return $this->hasMany(EmailFails::className(), ['email_notification_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
