<?php

namespace common\models;

use Yii;
use common\models\notifications\EmailFails;

/**
 * This is the model class for table "email_queue".
 *
 * @property int $id
 * @property string $to
 * @property string $from
 * @property string $subject
 * @property string $body
 * @property int $order_id
 * @property string $message_id
 * @property int $status
 * @property int $email_fail_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property EmailFails $emailFail
 * @property Order $order
 */
class EmailQueue extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_FAILED = 3; 
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_queue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['to', 'from'], 'required'],
            [['body'], 'string'],
            [['order_id', 'status', 'email_fail_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['to', 'from', 'subject', 'message_id'], 'string', 'max' => 255],
            [['message_id'], 'unique'],
            [['email_fail_id'], 'exist', 'skipOnError' => true, 'targetClass' => EmailFails::className(), 'targetAttribute' => ['email_fail_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'to' => Yii::t('app', 'To'),
            'from' => Yii::t('app', 'From'),
            'subject' => Yii::t('app', 'Subject'),
            'body' => Yii::t('app', 'Body'),
            'order_id' => Yii::t('app', 'Order ID'),
            'message_id' => Yii::t('app', 'Message ID'),
            'status' => Yii::t('app', 'Status'),
            'email_fail_id' => Yii::t('app', 'Email Fail ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailFail()
    {
        return $this->hasOne(EmailFails::className(), ['id' => 'email_fail_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
