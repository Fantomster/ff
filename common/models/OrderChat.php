<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_chat".
 *
 * @property integer $id
 * @peoperty integer $recipient_id
 * @property integer $order_id
 * @property integer $sent_by_id
 * @property integer $is_system
 * @property string $message
 * @property string $created_at
 * @property integer $viewed
 * @property integer $danger
 *
 * @property Order $order
 * @property User $sentBy
 */
class OrderChat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_chat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'sent_by_id'], 'required'],
            [['order_id', 'sent_by_id', 'viewed', 'recipient_id'], 'integer'],
            [['message', 'created_at', 'is_system', 'danger'], 'safe'],
            [['message'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process', 'on' => 'userSent'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['sent_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sent_by_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'sent_by_id' => 'Sent By ID',
            'is_system' => 'Is System',
            'message' => 'Message',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSentBy()
    {
        return $this->hasOne(User::className(), ['id' => 'sent_by_id']);
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'sent_by_id']);
    }

    public function getRecipient() {
        return $this->hasOne(Organization::className(), ['id' => 'recipient_id']);
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if (!is_a(Yii::$app, 'yii\console\Application')) {
                \api\modules\v1\modules\mobile\components\notifications\NotificationChat::actionSendMessage($this->id);
        }
    }
}
