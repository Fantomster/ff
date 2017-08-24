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
 * @property Profile $createdByProfile
 * @property Profile $acceptedByProfile
 * @property Organization $client
 * @property Organization $vendor
 * @property OrderContent[] $orderContent
 * @property OrderChat[] $orderChat
 * @property integer positionCount
 * @property string $statusText
 * @property bool $isObsolete
 * @property string $rawPrice
 * @property User[] $recipientsList
 */
class Order extends \yii\db\ActiveRecord {

    const STATUS_AWAITING_ACCEPT_FROM_VENDOR = 1;
    const STATUS_AWAITING_ACCEPT_FROM_CLIENT = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_DONE = 4;
    const STATUS_REJECTED = 5;
    const STATUS_CANCELLED = 6;
    const STATUS_FORMING = 7;
    const DISCOUNT_NO_DISCOUNT = null;
    const DISCOUNT_FIXED = 1;
    const DISCOUNT_PERCENT = 2;
    const DELAY_WITH_DELIVERY_DATE = 86400; //sec - 1 day
    const DELAY_WITHOUT_DELIVERY_DATE = 86400; //sec - 1 day

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

    public function beforeSave($insert) {
        $result = parent::beforeSave($insert);
        $this->discount = abs((int) $this->discount);
        return $result;
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
        return $this->hasMany(OrderChat::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    //check if order is obsolete i.e. can be set as done from any state by any side
    public function getIsObsolete() {
        if (in_array($this->status, [self::STATUS_DONE, self::STATUS_REJECTED, self::STATUS_CANCELLED, self::STATUS_FORMING])) {
            return false;
        }
        $today = time();
        if (empty($this->requested_delivery)) {
            $updatedAt = strtotime($this->updated_at);
            $interval = $today - $updatedAt;
            return $interval > self::DELAY_WITHOUT_DELIVERY_DATE;
        } else {
            $deliveryDate = strtotime($this->requested_delivery);
            $interval = $today - $deliveryDate;
            return $interval > self::DELAY_WITH_DELIVERY_DATE;
        }
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

    public function getStatusText() {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList() {
        return [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR => 'Ожидает подтверждения поставщика',
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT => 'Ожидает подтверждения клиента',
            Order::STATUS_PROCESSING => 'Выполняется',
            Order::STATUS_DONE => 'Завершен',
            Order::STATUS_REJECTED => 'Отклонен поставщиком',
            Order::STATUS_CANCELLED => 'Отменен клиентом',
            Order::STATUS_FORMING => 'Формируется',
        ];
    }

    public static function getStatusColors() {
        return [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR => '#368CBF',
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT => '#f39c12',
            Order::STATUS_PROCESSING => '#ccc',
            Order::STATUS_DONE => '#7EBC59',
            Order::STATUS_REJECTED => '#FB3640',
            Order::STATUS_CANCELLED => '#FF1111',
            Order::STATUS_FORMING => '#999999',
        ];
    }

    public function getPositionCount() {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id'])->count();
    }

    public function calculateDelivery() {
        $total_price = OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
        if (isset($this->vendor->delivery)) {
            $free_delivery = $this->vendor->delivery->min_free_delivery_charge;
        } else {
            $free_delivery = 0;
        }
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            return $this->vendor->delivery->delivery_charge;
        }
        return 0;
    }

    public function forFreeDelivery() {
        if (isset($this->vendor->delivery)) {
            $diff = $this->vendor->delivery->min_free_delivery_charge - $this->rawPrice;
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function forMinOrderPrice() {
        if (isset($this->vendor->delivery)) {
            $diff = $this->vendor->delivery->min_order_price - $this->rawPrice;
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function getRawPrice() {
        return OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
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
        $this->total_price = number_format($total_price, 2, '.', '');
        $this->save();
        return $this->total_price;
    }

    public function getFormattedDiscount() {
        switch ($this->discount_type) {
            case self::DISCOUNT_NO_DISCOUNT:
                return false;
            case self::DISCOUNT_FIXED:
                return $this->discount . " руб";
            case self::DISCOUNT_PERCENT:
                return $this->discount . "%";
        }
    }

    /**
     * @return User[] 
     */
    public function getRecipientsList() {
        $recipients[] = $this->createdBy;
        if (isset($this->accepted_by_id)) {
            $recipients[] = $this->acceptedBy;
        } else {
            $recipients = array_merge($recipients, $this->vendor->users);
        }
        return $recipients;
    }


    public function getOrdersExportColumns(){
        return [
            [
                'label' => 'Номер',
                'value' => 'id',
            ],
            [
                'label' => 'Ресторан',
                'value' => 'client.name',
            ],
            [
                'label' => 'Поставщик',
                'value' => 'vendor.name',
            ],
            [
                'label' => 'Заказ создал',
                'value' => 'createdByProfile.full_name',
            ],
            [
                'label' => 'Заказ принял',
                'value' => 'acceptedByProfile.full_name',
            ],
            [
                'label' => 'Сумма',
                'value' => 'total_price',
            ],
            [
                'label' => 'Дата создания',
                'value' => 'created_at',
            ],
            [
                'label' => 'Статус',
                'value' => function($data) {
                    return Order::statusText($data['status']);
                },
            ],
        ];
    }
}
