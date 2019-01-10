<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_chat".
 *
 * @property int $id Идентификатор записи в таблице
 * @property int $order_id Идентификатор заказа
 * @property int $sent_by_id Идентификатор пользователя, создавшего сообщение в чате
 * @property int $is_system Является ли сообщение созданным системой (0 - не является, 1- является)
 * @property string $message Текст сообщения в чате
 * @property string $created_at Дата и время создания записи в таблице
 * @property int $viewed Показатель статуса просмотренности сообщения в чате (0 - не просмотрено, 1 - просмотрено)
 * @property int $recipient_id Идентификатор пользователя, которому адресовано сообщение в чате
 * @property int $danger Показатель статуса важности сообщения в чате (0 - не является важным, 1 - является важным)
 *
 * @property Organization $recipient
 * @property Order $order
 * @property User $sentBy
 */
class OrderChat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_chat}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipient() {
        return $this->hasOne(Organization::className(), ['id' => 'recipient_id']);
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if (!is_a(Yii::$app, 'yii\console\Application')) {
                \api\modules\v1\modules\mobile\components\notifications\NotificationChat::actionSendMessage($this->id);
        }
    }
}
