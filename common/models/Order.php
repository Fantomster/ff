<?php

namespace common\models;

use Yii;
use yii\helpers\Url;

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
 * @property integer $currency_id
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
 * @property Currency $currency
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
            'total_price' => Yii::t('app', 'common.models.total_price', ['ru'=>'Итоговая цена']),
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
        $text = Yii::t('app', 'common.models.undefined', ['ru'=>'Неопределен']);
        switch ($status) {
            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                $text = Yii::t('app', 'common.models.new', ['ru'=>'Новый']);
                break;
            case Order::STATUS_PROCESSING:
                $text = Yii::t('app', 'common.models.in_process', ['ru'=>'Выполняется']);
                break;
            case Order::STATUS_DONE:
                $text = Yii::t('app', 'common.models.done', ['ru'=>'Завершен']);
                break;
            case Order::STATUS_REJECTED:
            case Order::STATUS_CANCELLED:
                $text = Yii::t('app', 'common.models.canceled', ['ru'=>'Отменен']);
                break;
        }
        return $text;
    }

    public function discountDropDown() {
        return [
            '' => Yii::t('app', 'common.models.no_discount', ['ru'=>'Без скидки']),
            '1' => Yii::t('app', 'common.models.discount_rouble_two', ['ru'=>'Скидка ({symbol})', 'symbol' => $this->currency->symbol]),
            '2' => Yii::t('app', 'common.models.discount_percent_two', ['ru'=>'Скидка (%)']),
        ];
    }

    public function getStatusText() {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList() {
        return [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.waiting', ['ru'=>'Ожидает подтверждения поставщика']),
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru'=>'Ожидает подтверждения клиента']),
            Order::STATUS_PROCESSING => Yii::t('app', 'common.models.in_process_two', ['ru'=>'Выполняется']),
            Order::STATUS_DONE => Yii::t('app', 'common.models.done_two', ['ru'=>'Завершен']),
            Order::STATUS_REJECTED => Yii::t('app', 'common.models.vendor_canceled', ['ru'=>'Отклонен поставщиком']),
            Order::STATUS_CANCELLED => Yii::t('app', 'common.models.client_canceled', ['ru'=>'Отменен клиентом']),
            Order::STATUS_FORMING => Yii::t('app', 'common.models.forming', ['ru'=>'Формируется']),
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
            $test = $this->vendor->delivery->delivery_charge;
            return $this->vendor->delivery->delivery_charge;
        }
        return 0;
    }

    public function forFreeDelivery() {
        if ($this->vendor->delivery->min_free_delivery_charge == 0) {
            return -1;
        }
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
                return $this->discount . $this->currency->symbol;
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
            $associatedManagers = $this->client->getAssociatedManagers($this->vendor_id);
            if (empty($associatedManagers)) {
                foreach ($this->vendor->users as $user) {
                    if ($user->role_id !== Role::ROLE_SUPPLIER_EMPLOYEE) {
                        $recipients[] = $user;
                    }
                }
            } else {
                $recipients = array_merge($recipients, $associatedManagers);
            }
        }

        //Получаем дополнительные Емайлы для рассылки
        //Для заказчика
        $recipients = array_merge($recipients, $this->client->additionalEmail);
        //Для поставщика
        $recipients = array_merge($recipients, $this->vendor->additionalEmail);

        return $recipients;
    }

    public function getOrdersExportColumns() {
        return [
            [
                'label' => Yii::t('app', 'common.models.number', ['ru'=>'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('app', 'common.models.rest', ['ru'=>'Ресторан']),
                'value' => 'client.name',
            ],
            [
                'label' => Yii::t('app', 'common.models.vendor_two', ['ru'=>'Поставщик']),
                'value' => 'vendor.name',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_created', ['ru'=>'Заказ создал']),
                'value' => 'createdByProfile.full_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_accepted', ['ru'=>'Заказ принял']),
                'value' => 'acceptedByProfile.full_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.sum', ['ru'=>'Сумма']),
                'value' => 'total_price',
            ],
            [
                'label' => Yii::t('app', 'common.models.creating_date', ['ru'=>'Дата создания']),
                'value' => 'created_at',
            ],
            [
                'label' => Yii::t('app', 'common.models.status_two', ['ru'=>'Статус']),
                'value' => function($data) {
                    return Order::statusText($data['status']);
                },
            ],
        ];
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if(isset($changedAttributes['discount']) && (($changedAttributes['discount'] == $this->discount) && (count($changedAttributes) == 0)))
                \api\modules\v1\modules\mobile\components\notifications\NotificationOrder::actionOrder($this->id, $insert);
        }
    }

    /**
     * @param $user
     * @return string
     */
    public function getUrlForUser($user)
    {
        if ($user instanceof User) {
            if (empty($user) || (!in_array($user->organization_id, [$this->client_id, $this->vendor_id]))) {
                return '';
            }
            switch ($user->status) {
                case User::STATUS_UNCONFIRMED_EMAIL:
                    $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                        "/order/view",
                        "id" => $this->id,
                        "token" => $user->access_token
                    ]);
                    break;
                default:
                    $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                        "/order/view",
                        "id" => $this->id
                    ]);
            }
            return $url;
        }

        //Если пришла модель с дополнительного Емайла
        if($user instanceof AdditionalEmail){
            return Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                "/order/view",
                "id" => $this->id
            ]);
        }
    }
    
    public function getCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }
    
    public function formatPrice() {
        return $this->total_price . " " . $this->currency->symbol;
    }
}
