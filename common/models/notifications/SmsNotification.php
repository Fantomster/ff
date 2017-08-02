<?php

namespace common\models\notifications;

use Yii;

/**
 * This is the model class for table "sms_notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $orders
 * @property integer $requests
 * @property integer $changes
 * @property integer $invites
 *
 * @property User $user
 */
class SmsNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'active', 'orders', 'requests', 'changes', 'invites'], 'integer'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
