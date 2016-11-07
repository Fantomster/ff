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
 * @property string $total_price
 * @property string $created_at
 * @property string $updated_at
 * @property string $requested_delivery
 * @property string $actual_delivery
 * @property string $comment
 * @property string $discount
 * @property integer $discount_type
 * 
 * @property User $acceptedBy
 * @property User $createdBy
 * @property string $createdByProfile
 * @property string $acceptedByProfile
 * @property Organization $client
 * @property Organization $vendor
 * @property OrderContent[] $orderContent
 * @property OrderChat[] $orderChat
 * @property integer positionCount
 */
class Order extends \yii\db\ActiveRecord {

    const STATUS_AWAITING_ACCEPT_FROM_VENDOR = 1;
    const STATUS_AWAITING_ACCEPT_FROM_CLIENT = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_DONE = 4;
    const STATUS_REJECTED = 5;
    const STATUS_CANCELLED = 6;
    const STATUS_FORMING = 7;
    const DISCOUNT_FIXED = 1;
    const DISCOUNT_PERCENT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'order';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
    public function rules() {
        return [
            [['client_id', 'vendor_id', 'status'], 'required'],
            [['client_id', 'vendor_id', 'created_by_id', 'status', 'discount_type'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['accepted_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['accepted_by_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'vendor_id' => 'Vendor ID',
            'created_by_id' => 'Created By ID',
            'accepted_by_id' => 'Accepted By ID',
            'status' => 'Status',
            'total_price' => 'Итоговая цена',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptedBy() {
        return $this->hasOne(User::className(), ['id' => 'accepted_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient() {
        return $this->hasOne(Organization::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile() {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptedByProfile() {
        return $this->hasOne(Profile::className(), ['user_id' => 'accepted_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderContent() {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderChat() {
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
                $text = 'Выполняется';
                break;
            case Order::STATUS_DONE:
                $text = 'Завершен';
                break;
            case Order::STATUS_REJECTED:
            case Order::STATUS_CANCELLED:
                $text = 'Отменен';
                break;
        }
        return $text;
    }
    
    public static function discountDropDown() {
        return [
            '' => 'Без скидки',
            '1' => 'Скидка (р)',
            '2' => 'Скидка (%)',
        ];
    }

    public function getPositionCount() {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id'])->count();
    }

    public function calculateDelivery() {
        $total_price = OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
        $free_delivery = $this->vendor->delivery->min_free_delivery_charge;
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            return $this->vendor->delivery->delivery_charge;
        }
        return 0;
    }
    
    public function calculateTotalPrice() {
        $total_price = OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
        if ($this->discount && ($this->discount_type == self::DISCOUNT_FIXED)) {
            $total_price -= $this->discount;
        }
        if ($this->discount && ($this->discount_type == self::DISCOUNT_PERCENT)) {
            $total_price = $total_price * (100 - $this->discount) / 100;
        }
        $free_delivery = $this->vendor->delivery->min_free_delivery_charge;
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            $total_price += $this->vendor->delivery->delivery_charge;
        }
        $this->total_price = $total_price;
        $this->save();
        return $this->total_price;
    }
}
