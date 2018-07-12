<?php

namespace common\models;

use common\components\EComIntegration;
use Yii;
use yii\web\BadRequestHttpException;

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
 * @property string $completion_date
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
 * @property integer invoice_relation
 * @property string $statusText
 * @property bool $isObsolete
 * @property string $rawPrice
 * @property User[] $recipientsList
 * @property Currency $currency
 * @property OrderAttachment[] $attachments
 * @property OrderAssignment $assignment
 */
class Order extends \yii\db\ActiveRecord
{

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

    public static function tableName()
    {
        return 'order';
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
    public function rules(): array
    {
        return [
            [['client_id', 'vendor_id', 'status'], 'required'],
            [['client_id', 'vendor_id', 'created_by_id', 'status', 'discount_type', 'invoice_relation'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment', 'completion_date'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['accepted_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['accepted_by_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'Номер заказа'),
            'client_id' => 'Client ID',
            'vendor_id' => 'Vendor ID',
            'created_by_id' => 'Created By ID',
            'accepted_by_id' => 'Accepted By ID',
            'status' => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'status_text' => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'total_price' => Yii::t('app', 'common.models.total_price', ['ru' => 'Итоговая цена']),
            'created_at' => Yii::t('app', 'Дата создания'),
            'updated_at' => 'Updated At',
            'vendor' => Yii::t('app', 'Поставщик'),
            'create_user' => Yii::t('app', 'Заказ создал'),
            'plan_price' => Yii::t('app', 'План'),
        ];
    }

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if ($this->discount_type == Order::DISCOUNT_FIXED) {
            $this->discount = round($this->discount, 2);
        } else {
            $this->discount = abs((int)$this->discount);
        }
        return $result;
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
    public function getEdiOrder()
    {
        return $this->hasOne(EdiOrder::className(), ['order_id' => 'id']);
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
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'accepted_by_id']);
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
        return $this->hasMany(OrderChat::className(), ['order_id' => 'id'])->orderBy([OrderChat::tableName() . '.created_at' => SORT_ASC]);
    }

    /**
     * @return int
     */
    public function getOrderChatCount()
    {
        return count($this->orderChat);
    }

    /**
     * @return int
     */
    public function getOrderChatUnreadCount($r_id)
    {
        return OrderChat::find()->where(['order_id' => $this->id, 'viewed' => 0, 'recipient_id' => $r_id])->count();
    }

    /**
     * @return int
     */
    public function getOrderChatLastMessage()
    {
        return $this->getOrderChat()->orderBy(['created_at' => SORT_DESC])->one();
    }

    //check if order is obsolete i.e. can be set as done from any state by any side
    public function getIsObsolete()
    {
        if (in_array($this->status, [self::STATUS_DONE, self::STATUS_REJECTED, self::STATUS_CANCELLED, self::STATUS_FORMING])) {
            return false;
        }
        if (Yii::$app->user->identity->organization->type_id == Organization::TYPE_RESTAURANT)
            return true;

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

    public static function statusText($status)
    {
        $text = Yii::t('app', 'common.models.undefined', ['ru' => 'Неопределен']);
        switch ($status) {
            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                $text = Yii::t('app', 'common.models.new', ['ru' => 'Новый']);
                break;
            case Order::STATUS_PROCESSING:
                $text = Yii::t('app', 'common.models.in_process', ['ru' => 'Выполняется']);
                break;
            case Order::STATUS_DONE:
                $text = Yii::t('app', 'common.models.done', ['ru' => 'Завершен']);
                break;
            case Order::STATUS_REJECTED:
            case Order::STATUS_CANCELLED:
                $text = Yii::t('app', 'common.models.canceled', ['ru' => 'Отменен']);
                break;
        }
        return $text;
    }

    public function discountDropDown()
    {
        return [
            '' => Yii::t('app', 'common.models.no_discount', ['ru' => 'Без скидки']),
            '1' => Yii::t('app', 'common.models.discount_rouble_two', ['ru' => 'Скидка ({symbol})', 'symbol' => $this->currency->symbol]),
            '2' => Yii::t('app', 'common.models.discount_percent_two', ['ru' => 'Скидка (%)']),
        ];
    }

    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList($short = false)
    {
        $result = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.waiting', ['ru' => 'Ожидает подтверждения поставщика']),
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru' => 'Ожидает подтверждения клиента']),
            Order::STATUS_PROCESSING => Yii::t('app', 'common.models.in_process_two', ['ru' => 'Выполняется']),
            Order::STATUS_DONE => Yii::t('app', 'common.models.done_two', ['ru' => 'Завершен']),
            Order::STATUS_REJECTED => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отклонен поставщиком']),
            Order::STATUS_CANCELLED => Yii::t('app', 'common.models.client_canceled', ['ru' => 'Отменен клиентом']),
        ];
        if (!$short) {
            $result[Order::STATUS_FORMING] = Yii::t('app', 'common.models.forming', ['ru' => 'Формируется']);
        }
        return $result;
    }

    public static function getStatusColors()
    {
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

    public function getPositionCount()
    {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id'])->count();
    }

    public function calculateDelivery()
    {
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

    public function forFreeDelivery($rawPrice = null)
    {
        if ($this->vendor->delivery->min_free_delivery_charge == 0) {
            return -1;
        }
        if (isset($this->vendor->delivery)) {
            $diff = $this->vendor->delivery->min_free_delivery_charge - (!isset($rawPrice) ? $this->rawPrice : $rawPrice);
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function forMinOrderPrice($rawPrice = null)
    {
        if (isset($this->vendor->delivery)) {
            $diff = $this->vendor->delivery->min_order_price - (!isset($rawPrice) ? $this->rawPrice : $rawPrice);
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    public function getRawPrice()
    {
        return OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
    }

    public function calculateTotalPrice($save = true, $rawPrice = null)
    {
        $total_price = !isset($rawPrice) ? OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar() : $rawPrice;
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
        if ($save) {
            $this->save();
        }
        return $this->total_price;
    }

    public function getTotalPriceWithOutDiscount()
    {
        $total_price = OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
        $free_delivery = $this->vendor->delivery->min_free_delivery_charge;
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            $total_price += floatval($this->vendor->delivery->delivery_charge);
        }
        return number_format(round($total_price, 2), 2, '.', '');
    }

    public function getFormattedDiscount($iso_code = false)
    {
        switch ($this->discount_type) {
            case self::DISCOUNT_NO_DISCOUNT:
                return false;
            case self::DISCOUNT_FIXED:
                return number_format(round($this->discount, 2), 2, '.', '') . ' ' . ($iso_code ? $this->currency->iso_code : $this->currency->symbol);
            case self::DISCOUNT_PERCENT:
                return $this->discount . "%";
        }
    }

    /**
     * @return User[]
     */
    public function getRecipientsList()
    {
        $recipients[] = $this->createdBy;
        if (isset($this->accepted_by_id)) {
            $recipients[] = $this->acceptedBy;
        } else {
            $associatedManagers = $this->client->getAssociatedManagers($this->vendor_id);
            if (empty($associatedManagers)) {
                foreach ($this->vendor->users as $user) {
                    if ($user->role_id !== Role::ROLE_SUPPLIER_EMPLOYEE && $user->role_id !== Role::ROLE_SUPPLIER_MANAGER) {
                        $recipients[] = $user;
                    }
                }
            } else {
                $recipients = array_merge($recipients, $associatedManagers);
            }
        }

        //Получаем дополнительные Емайлы для рассылки
        //Для заказчика
        if (!empty($this->client->additionalEmail)) {
            foreach ($this->client->additionalEmail as $addEmail) {
                $recipients[] = $addEmail;
            }
        }
        //Для поставщика
        if (!empty($this->vendor->additionalEmail)) {
            foreach ($this->vendor->additionalEmail as $addEmail) {
                $recipients[] = $addEmail;
            }
        }

        $result = [];
        foreach ($recipients as $recipient) {
            $result[] = $recipient;
        }

        return $result;
    }

    public function getOrdersExportColumns()
    {
        return [
            [
                'label' => Yii::t('app', 'common.models.number', ['ru' => 'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('app', 'common.models.rest', ['ru' => 'Ресторан']),
                'value' => 'client.name',
            ],
            [
                'label' => Yii::t('app', 'common.models.vendor_two', ['ru' => 'Поставщик']),
                'value' => 'vendor.name',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_created', ['ru' => 'Заказ создал']),
                'value' => 'createdByProfile.full_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.order_accepted', ['ru' => 'Заказ принял']),
                'value' => 'acceptedByProfile.full_name',
            ],
            [
                'label' => Yii::t('app', 'common.models.sum', ['ru' => 'Сумма']),
                'value' => 'total_price',
            ],
            [
                'label' => Yii::t('app', 'common.models.creating_date', ['ru' => 'Дата создания']),
                'value' => 'created_at',
            ],
            [
                'label' => Yii::t('app', 'common.models.status_two', ['ru' => 'Статус']),
                'value' => function ($data) {
                    return Order::statusText($data['status']);
                },
            ],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if (isset($changedAttributes['discount']) && (($changedAttributes['discount'] == $this->discount) && (count($changedAttributes) == 0))) {
                if ($this->status != self::STATUS_FORMING) {
                    \api\modules\v1\modules\mobile\components\notifications\NotificationOrder::actionOrder($this->id, $insert);
                } else {
                    \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCart($this->id, $insert);
                }
            }
        }

        if ($this->status != self::STATUS_FORMING && !$insert && (key_exists('total_price', $changedAttributes) || $this->status == self::STATUS_DONE)) {
            $vendor = Organization::findOne(['id' => $this->vendor_id]);
            $client = Organization::findOne(['id' => $this->client_id]);
            $errorText = Yii::t('app', 'common.models.order.gln', ['ru' => 'Внимание! Выбранный Поставщик работает с Заказами в системе электронного документооборота. Вам необходимо зарегистрироваться в системе EDI и получить GLN-код']);
            if (isset($client->ediOrganization->gln_code) && isset($vendor->ediOrganization->gln_code) && $client->ediOrganization->gln_code > 0 && $vendor->ediOrganization->gln_code > 0) {
                $eComIntegration = new EComIntegration();
                $login = $vendor->ediOrganization->login;
                $pass = $vendor->ediOrganization->pass;
                if($this->status == self::STATUS_DONE){
                    $result = $eComIntegration->sendOrderInfo($this, $vendor, $client, $login, $pass, true);
                }else{
                    $result = $eComIntegration->sendOrderInfo($this, $vendor, $client, $login, $pass);
                }
                if (!$result) {
                    Yii::error(Yii::t('app', 'common.models.order.edi_error'));
                }
            }
            if ((!isset($client->ediOrganization->gln_code) || empty($client->ediOrganization->gln_code)) && isset($vendor->ediOrganization->gln_code)) {
                throw new BadRequestHttpException($errorText);
            }
        }
    }


    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($this->status == self::STATUS_FORMING) {
                \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCart($this->id);
            }
        }
    }

    /**
     * @param $user
     * @return string
     */
    public function getUrlForUser($user)
    {
        if ($user instanceof User) {

            if (Yii::$app instanceof Yii\console\Application){
                return Yii::$app->params['url'] . "/order/view" . $this->id;
            }else{
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view/",
                    "id" => $this->id
                ]);
            }

            if ($user->status == User::STATUS_UNCONFIRMED_EMAIL) {
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view",
                    "id" => $this->id,
                    "token" => $user->access_token
                ]);
            }

            //Если получает заказчик, и он не работает в системе, добавляем токен
            if ($user->organization_id == $this->vendor_id && $this->vendor->is_work == 0) {
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view",
                    "id" => $this->id,
                    "token" => $user->access_token
                ]);
            }

            return $url;
        }

        //Если пришла модель с дополнительного Емайла
        if ($user instanceof AdditionalEmail) {
            return Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                "/order/view",
                "id" => $this->id
            ]);
        }
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    public function getInvoice()
    {
        return $this->hasOne(IntegrationInvoice::className(), ['order_id' => 'id']);
    }

    public function getInvoiceRelation()
    {
        return $this->hasOne(IntegrationInvoice::className(), ['id' => 'invoice_relation']);
    }

    public function formatPrice()
    {
        return $this->total_price . " " . $this->currency->symbol;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(OrderAttachment::className(), ['order_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignment() {
        return $this->hasOne(OrderAssignment::className(), ['order_id' => 'id']);
    }
}
