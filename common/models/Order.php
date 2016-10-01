<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $vendor_id
 * @property integer $created_by_id
 * @property integer $accepted_by_id
 * @property integer $status
 * @property integer $total_price
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $acceptedBy
 * @property Organization $client
 * @property User $createdBy
 * @property Organization $vendor
 * @property OrderContent[] $orderContent
 * @property OrderChat[] $orderChat
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_AWAITING_ACCEPT_FROM_VENDOR = 1;
    const STATUS_AWAITING_ACCEPT_FROM_CLIENT = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_DONE = 4;
    const STATUS_REJECTED = 5;
    const STATUS_CANCELLED = 6;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
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
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'vendor_id', 'created_by_id', 'status'], 'required'],
            [['client_id', 'vendor_id', 'created_by_id', 'status'], 'integer'],
            [['total_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['accepted_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['accepted_by_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'vendor_id' => 'Vendor ID',
            'created_by_id' => 'Created By ID',
            'accepted_by_id' => 'Accepted By ID',
            'status' => 'Status',
            'total_price' => 'Total Price',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'accepted_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderContent()
    {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderChat()
    {
        return $this->hasMany(OrderChat::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }
    
    public static function statusText($status) {
        $text = 'Неопределен';
        switch ($status) {
            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                $text = 'Новый';
                break;
            case Order::STATUS_PROCESSING:
                $text = 'Исполняется';
                break;
            case Order::STATUS_DONE:
                $text = 'Готов';
                break;
            case Order::STATUS_REJECTED:
            case Order::STATUS_CANCELLED:
                $text = 'Отменен';
                break;
        }
        return $text;
    }
}
