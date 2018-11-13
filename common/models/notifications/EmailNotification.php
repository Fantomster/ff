<?php

namespace common\models\notifications;

use Yii;
use common\models\User;

/**
 * This is the model class for table "email_notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $rel_user_org_id
 * @property integer $orders
 * @property integer $requests
 * @property integer $changes
 * @property integer $invites
 * @property integer $order_created
 * @property integer $order_canceled
 * @property integer $order_changed
 * @property integer $order_processing
 * @property integer $order_done
 * @property integer request_accept
 * @property integer $merc_vsd
 * @property integer $merc_stock_expiry
 * @property string $created_at
 * @property string $updated_at
 *
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
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
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
            [['rel_user_org_id'], 'required'],
            [['user_id', 'orders', 'requests', 'changes', 'invites', 'order_created', 'order_canceled', 'order_changed', 'order_processing', 'order_done', 'request_accept', 'merc_vsd', 'receive_employee_email', 'rel_user_org_id', 'merc_stock_expiry'], 'integer'],
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

    public static function emptyInstance() {
        return new self([
            'order_created' => 0,
            'order_canceled' => 0,
            'order_changed' => 0,
            'order_processing' => 0,
            'order_done' => 0
        ]);
    }

}
