<?php

namespace common\models;

use api_web\components\Registry;
use common\components\edi\EDIIntegration;
use common\components\EComIntegration;
use common\helpers\DBNameHelper;
use frontend\modules\clientintegr\components\AutoWaybillHelper;
use Yii;
use yii\behaviors\AttributesBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "order".
 *
 * @property integer           $id
 * @property integer           $client_id
 * @property integer           $vendor_id
 * @property integer           $created_by_id
 * @property integer           $accepted_by_id
 * @property integer           $status
 * @property string            $total_price
 * @property string            $created_at
 * @property string            $updated_at
 * @property string            $completion_date
 * @property string            $requested_delivery
 * @property string            $actual_delivery
 * @property string            $comment
 * @property string            $discount
 * @property integer           $discount_type
 * @property integer           $currency_id
 * @property string            $waybill_number
 * @property integer           $service_id
 * @property string            $status_updated_at
 * @property string            $edi_order
 * @property string            $edi_ordersp
 * @property User              $acceptedBy
 * @property User              $createdBy
 * @property Profile           $createdByProfile
 * @property Profile           $acceptedByProfile
 * @property Organization      $client
 * @property Organization      $vendor
 * @property OrderContent[]    $orderContent
 * @property OrderChat[]       $orderChat
 * @property integer           positionCount
 * @property integer           invoice_relation
 * @property string            $statusText
 * @property bool              $isObsolete
 * @property string            $rawPrice
 * @property User[]            $recipientsList
 * @property Currency          $currency
 * @property OrderAttachment[] $attachments
 * @property OrderAssignment   $assignment
 * @property EmailQueue[]      $relatedEmails
 */
class Order extends \yii\db\ActiveRecord
{

    const STATUS_AWAITING_ACCEPT_FROM_VENDOR = 1;
    const STATUS_AWAITING_ACCEPT_FROM_CLIENT = 2;
    const STATUS_PROCESSING                  = 3;
    const STATUS_DONE                        = 4;
    const STATUS_REJECTED                    = 5;
    const STATUS_CANCELLED                   = 6;
    const STATUS_FORMING                     = 7;
    const STATUS_EDI_SENT_BY_VENDOR          = 8;
    const STATUS_EDI_ACCEPTANCE_FINISHED     = 9;
    const STATUS_EDI_SENDING_TO_VENDOR       = 10;
    const STATUS_EDI_SENDING_ERROR           = 11;
    const DISCOUNT_NO_DISCOUNT               = null;
    const DISCOUNT_FIXED                     = 1;
    const DISCOUNT_PERCENT                   = 2;
    const DELAY_WITH_DELIVERY_DATE           = 86400; //sec - 1 day
    const DELAY_WITHOUT_DELIVERY_DATE        = 86400; //sec - 1 day

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
            'timestamp'  => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
            'attributes' => [
                'class'      => AttributesBehavior::class,
                'attributes' => [
                    'status_updated_at' => [
                        ActiveRecord::EVENT_BEFORE_UPDATE => function ($event, $attribute) {
                            if ($this->status != $this->oldAttributes['status']) {
                                $this->$attribute = gmdate("Y-m-d H:i:s");
                            }
                        },
                    ],
                ],
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
            [['client_id', 'vendor_id', 'created_by_id', 'status', 'discount_type', 'invoice_relation', 'service_id'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'status_updated_at', 'updated_at', 'edi_order', 'requested_delivery', 'actual_delivery', 'comment', 'completion_date', 'waybill_number', 'edi_ordersp'], 'safe'],
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
            'id'             => Yii::t('app', 'Номер заказа'),
            'client_id'      => 'Client ID',
            'vendor_id'      => 'Vendor ID',
            'created_by_id'  => 'Created By ID',
            'accepted_by_id' => 'Accepted By ID',
            'status'         => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'status_text'    => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'total_price'    => Yii::t('app', 'common.models.total_price', ['ru' => 'Итоговая цена']),
            'created_at'     => Yii::t('app', 'Дата создания'),
            'updated_at'     => 'Updated At',
            'vendor'         => Yii::t('app', 'Поставщик'),
            'create_user'    => Yii::t('app', 'Заказ создал'),
            'plan_price'     => Yii::t('app', 'План'),
            'waybill_number' => Yii::t('app', 'Номер накладной'),
        ];
    }

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if ($this->discount_type == Order::DISCOUNT_FIXED) {
            $this->discount = round($this->discount, 2);
        } else {
            $this->discount = abs((int) $this->discount);
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
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id'])->indexBy('id');
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
        if (in_array($this->status, [OrderStatus::STATUS_DONE, OrderStatus::STATUS_REJECTED, OrderStatus::STATUS_CANCELLED, OrderStatus::STATUS_FORMING])) {
            return false;
        }
        if (Yii::$app->user->identity->organization->type_id == Organization::TYPE_RESTAURANT) {
            return true;
        }
        $today = time();
        if (empty($this->requested_delivery)) {
            $updatedAt = strtotime($this->updated_at);
            $interval  = $today - $updatedAt;
            return $interval > self::DELAY_WITHOUT_DELIVERY_DATE;
        } else {
            $deliveryDate = strtotime($this->requested_delivery);
            $interval     = $today - $deliveryDate;
            return $interval > self::DELAY_WITH_DELIVERY_DATE;
        }
    }

    public static function statusText($status)
    {
        $text = Yii::t('app', 'common.models.undefined', ['ru' => 'Неопределен']);
        switch ($status) {
            case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                $text = Yii::t('app', 'common.models.new', ['ru' => 'Новый']);
                break;
            case OrderStatus::STATUS_PROCESSING:
                $text = Yii::t('app', 'common.models.in_process', ['ru' => 'Выполняется']);
                break;
            case OrderStatus::STATUS_DONE:
                $text = Yii::t('app', 'common.models.done', ['ru' => 'Завершен']);
                break;
            case OrderStatus::STATUS_REJECTED:
            case OrderStatus::STATUS_CANCELLED:
                $text = Yii::t('app', 'common.models.canceled', ['ru' => 'Отменен']);
                break;
        }
        return $text;
    }

    public function discountDropDown()
    {
        return [
            ''  => Yii::t('app', 'common.models.no_discount', ['ru' => 'Без скидки']),
            '1' => Yii::t('app', 'common.models.discount_rouble_two', ['ru' => 'Скидка ({symbol})', 'symbol' => $this->currency->symbol]),
            '2' => Yii::t('app', 'common.models.discount_percent_two', ['ru' => 'Скидка (%)']),
        ];
    }

    public function getStatusText()
    {
        if (in_array($this->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            $statusList = self::getStatusListEdo();
        } else {
            $statusList = self::getStatusList();
        }
        return $statusList[$this->status];
    }

    public static function getStatusListEdo()
    {
        return [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.waiting', ['ru' => 'Ожидает подтверждения']),
            OrderStatus::STATUS_PROCESSING                  => Yii::t('app', 'common.models.in_process_two', ['ru' => 'Выполняются']),
            OrderStatus::STATUS_EDI_SENT_BY_VENDOR          => Yii::t('app', 'common.models.order_status.status_edo_sent_by_vendor', ['ru' => 'Отправлен поставщиком']),
            OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED     => Yii::t('app', 'common.models.order_status.status_edo_acceptance_finished', ['ru' => 'Приемка завершена']),
            OrderStatus::STATUS_DONE                        => Yii::t('app', 'common.models.done_two', ['ru' => 'Завершен']),
            OrderStatus::STATUS_CANCELLED                   => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отменен']),
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru' => 'Ожидает подтверждения клиента']),
            OrderStatus::STATUS_REJECTED                    => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отклонен поставщиком']),
        ];
    }

    public static function getStatusList($short = false)
    {
        $result = [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.waiting', ['ru' => 'Ожидает подтверждения поставщика']),
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru' => 'Ожидает подтверждения клиента']),
            OrderStatus::STATUS_PROCESSING                  => Yii::t('app', 'common.models.in_process_two', ['ru' => 'Выполняется']),
            OrderStatus::STATUS_DONE                        => Yii::t('app', 'common.models.done_two', ['ru' => 'Завершен']),
            OrderStatus::STATUS_REJECTED                    => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отклонен поставщиком']),
            OrderStatus::STATUS_CANCELLED                   => Yii::t('app', 'common.models.client_canceled', ['ru' => 'Отменен клиентом']),
        ];
        if (!$short) {
            $result[OrderStatus::STATUS_FORMING] = Yii::t('app', 'common.models.forming', ['ru' => 'Формируется']);
        }
        return $result;
    }

    public static function getStatusColors()
    {
        return [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR => '#368CBF',
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT => '#f39c12',
            OrderStatus::STATUS_PROCESSING                  => '#ccc',
            OrderStatus::STATUS_DONE                        => '#7EBC59',
            OrderStatus::STATUS_REJECTED                    => '#FB3640',
            OrderStatus::STATUS_CANCELLED                   => '#FF1111',
            OrderStatus::STATUS_FORMING                     => '#999999',
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
        $total_price   = OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
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
        $franchiseeClientsManagers = $this->client->getRelatedFranchisee();
        $franchiseeVendorsManagers = $this->vendor->getRelatedFranchisee();
        $recipients                = array_merge($recipients, $franchiseeClientsManagers, $franchiseeVendorsManagers);

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
                if ($this->status != OrderStatus::STATUS_FORMING) {
                    \api\modules\v1\modules\mobile\components\notifications\NotificationOrder::actionOrder($this->id, $insert);
                } else {
                    \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCart($this->id, $insert);
                }
            }
        }

        if ($this->status != OrderStatus::STATUS_FORMING && !$insert && (key_exists('total_price', $changedAttributes) || $this->status == OrderStatus::STATUS_DONE)) {
            $vendor    = Organization::findOne(['id' => $this->vendor_id]);
            $client    = Organization::findOne(['id' => $this->client_id]);
            $errorText = Yii::t('app', 'common.models.order.gln', ['ru' => 'Внимание! Выбранный Поставщик работает с Заказами в системе электронного документооборота. Вам необходимо зарегистрироваться в системе EDI и получить GLN-код']);
            if (isset($client->ediOrganization->gln_code) && isset($vendor->ediOrganization->gln_code) && $client->ediOrganization->gln_code > 0 && $vendor->ediOrganization->gln_code > 0) {
                if (strpos($vendor->name, 'est_Korus_Organization')) {
                    $eComIntegration = new EDIIntegration(['orgId' => $vendor->id]);
                } else {
                    $eComIntegration = new EComIntegration();
                }

                if ($this->status == OrderStatus::STATUS_DONE) {
                    $result = $eComIntegration->sendOrderInfo($this, true);
                } else {
                    $result = $eComIntegration->sendOrderInfo($this, false);
                }
                if (!$result) {
                    Yii::error(Yii::t('app', 'common.models.order.edi_error'));
                }
            }
            if ((!isset($client->ediOrganization->gln_code) || empty($client->ediOrganization->gln_code)) && isset($vendor->ediOrganization->gln_code)) {
                throw new BadRequestHttpException($errorText);
            }
        }
        if ($this->status == OrderStatus::STATUS_DONE) {
            AutoWaybillHelper::processWaybill($this->id);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($this->status == OrderStatus::STATUS_FORMING) {
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

            if (Yii::$app instanceof Yii\console\Application) {
                return Yii::$app->params['url'] . "/order/view" . $this->id;
            } else {
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view/",
                    "id" => $this->id
                ]);
            }

            if ($user->status == User::STATUS_UNCONFIRMED_EMAIL) {
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view",
                    "id"    => $this->id,
                    "token" => $user->access_token
                ]);
            }

            //Если получает вендор, и он не работает в системе, добавляем токен
            $relationExists = RelationUserOrganization::find()->where(['user_id' => $user->id, 'organization_id' => $this->vendor_id])->exists();
            if ($relationExists && (($this->vendor->blacklisted == Organization::STATUS_BLACKISTED) || ($this->vendor->blacklisted == Organization::STATUS_UNSORTED))) {
                $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view",
                    "id"    => $this->id,
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

    public function getOrganizationByUser($user)
    {
        $clientRelation = RelationUserOrganization::findOne(['user_id' => $user->id, 'organization_id' => $this->client_id]);
        if (isset($clientRelation)) {
            return $clientRelation->organization;
        }
        $vendorRelation = RelationUserOrganization::findOne(['user_id' => $user->id, 'organization_id' => $this->vendor_id]);
        if (isset($vendorRelation)) {
            return $vendorRelation->organization;
        }
        return null;
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
    public function getAssignment()
    {
        return $this->hasOne(OrderAssignment::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedEmails()
    {
        return $this->hasMany(EmailQueue::className(), ['order_id' => 'id']);
    }

    public function getFormattedCreationDate()
    {
        return Yii::$app->formatter->asDatetime($this->created_at, "php:d.m.Y, H:i");
    }

    /**
     * @param null $service_id
     * @return array|Waybill[]|ActiveRecord[]
     */
    public function getWaybills($service_id = null)
    {
        $db_instance = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db->dsn);

        $query = (new Query())
                ->distinct()
                ->select(['w.id'])
                ->from(Waybill::tableName() . ' as w')
                ->leftJoin(WaybillContent::tableName() . ' as wc', 'wc.waybill_id = w.id')
                ->innerJoin($db_instance . '.' . OrderContent::tableName() . ' as oc', 'oc.id = wc.order_content_id')
                ->where('oc.order_id = :id', [':id' => $this->id]);

        if ($service_id) {
            $query->andWhere('w.service_id = :s_id', [':s_id' => $service_id]);
        }

        return Waybill::find()->where(['in', 'id', $query->createCommand(\Yii::$app->db_api)->queryColumn()])->all() ?? [];
    }

}
