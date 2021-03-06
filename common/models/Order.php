<?php

namespace common\models;

use api_web\behaviors\OrderBehavior;
use api_web\components\Registry;
use common\components\edi\EDIIntegration;
use common\helpers\DBNameHelper;
use frontend\modules\clientintegr\components\AutoWaybillHelper;
use Yii;
use yii\behaviors\{
    AttributesBehavior, TimestampBehavior
};
use yii\db\{
    ActiveRecord, Query
};
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "order".
 *
 * @property int                $id                 Идентификатор записи в таблице
 * @property int                $client_id          Идентификатор клиента, оформившего заказ
 * @property int                $vendor_id          Идентификатор организации-поставщика
 * @property int                $created_by_id      Идентификатор пользователя, создавшего заказ
 * @property int                $accepted_by_id     Идентификатор пользователя, завершившего заказ
 * @property int                $status             Идентификатор статуса заказа
 * @property string             $total_price        Сумма заказа без НДС
 * @property int                $invoice_relation   Идентификатор накладной ТОРГ-12
 * @property string             $created_at         Дата и время создания записи в таблице
 * @property string             $updated_at         Дата и время последнего изменения записи в таблице
 * @property string             $requested_delivery Ожидаемые дата и время доставки товаров
 * @property string             $actual_delivery    Реальные дата и время доставки товаров
 * @property string             $comment            Комментарий (не используется)
 * @property string             $discount           Размер скидки в процентах
 * @property int                $discount_type      Идентификатор типа скидки
 * @property int                $currency_id        Идентификатор вида валюты, в которой оформлен заказ
 * @property string             $completion_date    Дата и время завершения заказа
 * @property string             $waybill_number     Номер приходной накладной
 * @property int                $service_id         Идентификатор учётного сервиса (all_service)
 * @property string             $status_updated_at  Дата и время последнего изменения статуса заказа
 * @property string             $edi_order          Идентификатор EDI заказа
 * @property string             $edi_ordersp        Название файла ordersp, который поступает от поставщика
 * @property string             $accepted_at        Дата и время завершения заказа
 * @property string             $edi_error_message  Сообщение об ошибке от EDI
 * @property int                $replaced_order_id  Идентификатор заказа, который был заменён текущим
 * @property string             $edi_doc_date       Дата накладной заказа по EDI
 * @property int                $is_recadv_sent     Показатель состояния отправки файла recadv (0 - не отправлен, 1 -
 *           отправлен)
 * @property int                $preorder_id
 * @property EmailQueue[]       $emailQueues
 * @property User               $acceptedBy
 * @property Organization       $client
 * @property User               $createdBy
 * @property Currency           $currency
 * @property Organization       $vendor
 * @property OrderAssignment    $orderAssignment
 * @property OrderAttachment[]  $orderAttachments
 * @property OrderChat[]        $orderChat
 * @property OrderContent[]     $orderContent
 * @property EdiOrder           $ediOrder
 * @property Profile            $createdByProfile
 * @property Profile            $acceptedByProfile
 * @property IntegrationInvoice $invoice
 * @property IntegrationInvoice $invoiceRelation
 * @property EmailQueue[]       $relatedEmails
 * @property int                $positionCount
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
    const STATUS_EDI_SENT_BY_VENDOR = 8;
    const STATUS_EDI_ACCEPTANCE_FINISHED = 9;
    const STATUS_EDI_SENDING_TO_VENDOR = 10;
    const STATUS_EDI_SENDING_ERROR = 11;
    const STATUS_PREORDER = OrderStatus::STATUS_PREORDER;
    const DISCOUNT_NO_DISCOUNT = null;
    const DISCOUNT_FIXED = 1;
    const DISCOUNT_PERCENT = 2;
    const DELAY_WITH_DELIVERY_DATE = 86400; //sec - 1 day
    const DELAY_WITHOUT_DELIVERY_DATE = 86400; //sec - 1 day

    public $ediProcessor = 0;
    public $count_message = null;
    public $unread_message = null;
    public $last_message = null;
    public $last_message_date = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                "class" => OrderBehavior::class,
                "model" => $this
            ],
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
                                return gmdate("Y-m-d H:i:s");
                            }
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['client_id', 'vendor_id', 'status'], 'required'],
            [['client_id', 'vendor_id', 'created_by_id', 'status', 'discount_type', 'invoice_relation', 'service_id', 'replaced_order_id'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'status_updated_at', 'updated_at', 'edi_order', 'requested_delivery', 'actual_delivery', 'comment', 'completion_date', 'waybill_number', 'edi_ordersp', 'edi_doc_date', 'is_recadv_sent'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['accepted_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['accepted_by_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'                => Yii::t('app', 'Номер заказа'),
            'replaced_order_id' => Yii::t('app', 'ID заказа который был заменен текущим'),
            'client_id'         => 'Client ID',
            'vendor_id'         => 'Vendor ID',
            'created_by_id'     => 'Created By ID',
            'accepted_by_id'    => 'Accepted By ID',
            'status'            => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'status_text'       => Yii::t('app', 'common.models.status', ['ru' => 'Статус']),
            'total_price'       => Yii::t('app', 'common.models.total_price', ['ru' => 'Итоговая цена']),
            'created_at'        => Yii::t('app', 'Дата создания'),
            'updated_at'        => 'Updated At',
            'vendor'            => Yii::t('app', 'Поставщик'),
            'create_user'       => Yii::t('app', 'Заказ создал'),
            'plan_price'        => Yii::t('app', 'План'),
            'waybill_number'    => Yii::t('app', 'Номер накладной'),
            'edi_doc_date'      => Yii::t('app', 'Дата накладной заказа по EDI')
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->discount_type == Order::DISCOUNT_FIXED) {
                $this->discount = round($this->discount, 2);
            } else {
                $this->discount = abs((int)$this->discount);
            }

            /**
             * Если ресторан и поставщик EDI
             * */
            if ($this->client->isEdi() && $this->vendor->isEdi()) {
                $this->service_id = Registry::EDI_SERVICE_ID;
            }

            return true;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailQueues()
    {
        return $this->hasMany(EmailQueue::className(), ['order_id' => 'id']);
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
     * @return array
     */
    public function getEdiNumber(): array
    {
        if (empty($this->orderContent)) {
            return [];
        }
        return array_values(array_filter(array_unique(array_map(function (OrderContent $oc) {
            return $oc->edi_number;
        }, $this->orderContent))));
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

    /**
     * @return bool
     */
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
            $interval = $today - $updatedAt;
            return $interval > self::DELAY_WITHOUT_DELIVERY_DATE;
        } else {
            $deliveryDate = strtotime($this->requested_delivery);
            $interval = $today - $deliveryDate;
            return $interval > self::DELAY_WITH_DELIVERY_DATE;
        }
    }

    /**
     * @param $status
     * @return string
     */
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

    /**
     * @return array
     */
    public function discountDropDown()
    {
        return [
            ''  => Yii::t('app', 'common.models.no_discount', ['ru' => 'Без скидки']),
            '1' => Yii::t('app', 'common.models.discount_rouble_two', ['ru' => 'Скидка ({symbol})', 'symbol' => $this->currency->symbol]),
            '2' => Yii::t('app', 'common.models.discount_percent_two', ['ru' => 'Скидка (%)']),
        ];
    }

    /**
     * @return mixed
     */
    public function getStatusText()
    {
        if (in_array($this->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            $statusList = self::getStatusListEdo();
        } else {
            $statusList = self::getStatusList();
        }
        return $statusList[$this->status];
    }

    /**
     * @return array
     */
    public static function getStatusListEdo()
    {
        return [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.order_status.status_awaiting_accept_from_vendor', ['ru' => 'Ожидает подтверждения']),
            OrderStatus::STATUS_PROCESSING                  => Yii::t('app', 'common.models.in_process_two', ['ru' => 'Выполняются']),
            OrderStatus::STATUS_EDI_SENT_BY_VENDOR          => Yii::t('app', 'common.models.order_status.status_edo_sent_by_vendor', ['ru' => 'Отправлен поставщиком']),
            OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED     => Yii::t('app', 'common.models.order_status.status_edo_acceptance_finished', ['ru' => 'Приемка завершена']),
            OrderStatus::STATUS_DONE                        => Yii::t('app', 'common.models.done_two', ['ru' => 'Завершен']),
            OrderStatus::STATUS_CANCELLED                   => Yii::t('app', 'common.models.canceled', ['ru' => 'Отменен']),
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru' => 'Ожидает подтверждения клиента']),
            OrderStatus::STATUS_REJECTED                    => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отклонен поставщиком']),
            OrderStatus::STATUS_PREORDER                    => Yii::t('app', 'common.models.order.status_preorder', ['ru' => 'Предзаказ']),
        ];
    }

    /**
     * @param bool $short
     * @return array
     */
    public static function getStatusList($short = false)
    {
        $result = [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR => Yii::t('app', 'common.models.waiting', ['ru' => 'Ожидает подтверждения поставщика']),
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT => Yii::t('app', 'common.models.waiting_client', ['ru' => 'Ожидает подтверждения клиента']),
            OrderStatus::STATUS_PROCESSING                  => Yii::t('app', 'common.models.in_process_two', ['ru' => 'Выполняется']),
            OrderStatus::STATUS_DONE                        => Yii::t('app', 'common.models.done_two', ['ru' => 'Завершен']),
            OrderStatus::STATUS_REJECTED                    => Yii::t('app', 'common.models.vendor_canceled', ['ru' => 'Отклонен поставщиком']),
            OrderStatus::STATUS_CANCELLED                   => Yii::t('app', 'common.models.client_canceled', ['ru' => 'Отменен клиентом']),
            OrderStatus::STATUS_PREORDER                    => Yii::t('app', 'common.models.order.status_preorder', ['ru' => 'Предзаказ']),
        ];
        if (!$short) {
            $result[OrderStatus::STATUS_FORMING] = Yii::t('app', 'common.models.forming', ['ru' => 'Формируется']);
        }
        return $result;
    }

    /**
     * @return array
     */
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

    /**
     * @return int|string
     */
    public function getPositionCount()
    {
        return $this->hasMany(OrderContent::className(), ['order_id' => 'id'])->count();
    }

    /**
     * @return int|string
     */
    public function calculateDelivery()
    {
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

    /**
     * @param null $rawPrice
     * @return float|int
     */
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

    /**
     * @param null $rawPrice
     * @return float|int
     */
    public function forMinOrderPrice($rawPrice = null)
    {
        if (isset($this->vendor->delivery)) {
            $diff = $this->vendor->delivery->min_order_price - (!isset($rawPrice) ? $this->rawPrice : $rawPrice);
        } else {
            $diff = 0;
        }
        return ceil((($diff > 0) ? $diff : 0) * 100) / 100;
    }

    /**
     * @return false|null|string
     */
    public function getRawPrice()
    {
        return OrderContent::find()->select('SUM(quantity*price)')->where(['order_id' => $this->id])->scalar();
    }

    /**
     * Пересчет total_price
     *
     * @param bool $save
     * @param null $rawPrice
     * @return string
     */
    public function calculateTotalPrice($save = true, $rawPrice = null)
    {
        if (is_null($rawPrice)) {
            $total_price = $this->getTotalPriceFromDb();
        } else {
            $total_price = $rawPrice;
        }

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

        $this->setAttribute('total_price', number_format($total_price, 2, '.', ''));
        if ($save) {
            $this->save();
        }

        return $this->getAttribute('total_price');
    }

    /**
     * @return string
     */
    public function getTotalPriceWithOutDiscount()
    {
        $total_price = $this->getTotalPriceFromDb();
        $free_delivery = $this->vendor->delivery->min_free_delivery_charge;
        if ((($free_delivery > 0) && ($total_price < $free_delivery)) || ($free_delivery == 0)) {
            $total_price += floatval($this->vendor->delivery->delivery_charge);
        }
        return number_format(round($total_price, 2), 2, '.', '');
    }

    /**
     * @param null $service_id
     * @return false|string|null
     */
    public function getTotalPriceFromDb($service_id = null)
    {
        $waybills = $this->getWaybills($service_id);
        if (!empty($waybills)) {
            $query_waybill = (new Query)
                ->select('sum_without_vat')
                ->from(WaybillContent::tableName() . ' as w')
                ->where('w.order_content_id = oc.id')
                ->orderBy(['updated_at' => SORT_DESC])
                ->limit(1)->createCommand()->getRawSql();

            $query = (new Query())
                ->select([
                    'waybill_sum' => "(" . $query_waybill . ")",
                    'oc_sum'      => '(oc.quantity * oc.price)'
                ])
                ->from(DBNameHelper::getMainName() . '.' . OrderContent::tableName() . ' as oc')
                ->where('oc.order_id = :o_id', [':o_id' => $this->id])
                ->createCommand()->getRawSql();

            $total_price = (new Query())
                ->select(['SUM(COALESCE(waybill_sum, oc_sum))'])
                ->from("(" . $query . ") as t")
                ->scalar(\Yii::$app->db_api);
        } else {
            $total_price = OrderContent::find()
                ->select('SUM(quantity*price)')
                ->where(['order_id' => $this->id])
                ->scalar();
        }
        return $total_price;
    }

    /**
     * @param bool $iso_code
     * @return bool|string
     */
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
        $recipients = [];
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
        $recipients = array_merge($recipients, $franchiseeClientsManagers, $franchiseeVendorsManagers);

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

        //Новые контакты, для ленивых поставщиков
        if ($this->vendor->type_id == Organization::TYPE_LAZY_VENDOR && !empty($this->vendor->organizationContact)) {
            /** @var OrganizationContact $model */
            foreach ($this->vendor->organizationContact as $model) {
                $recipients[] = $model;
            }
        }

        $result = [];
        foreach ($recipients as $recipient) {
            $result[] = $recipient;
        }

        return $result;
    }

    /**
     * @return array
     */
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

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     * @throws BadRequestHttpException
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        try {
            if (!is_a(Yii::$app, 'yii\console\Application')) {
                if (isset($changedAttributes['discount']) && (($changedAttributes['discount'] == $this->discount) && (count($changedAttributes) == 0))) {
                    if ($this->status != OrderStatus::STATUS_FORMING) {
                        \api\modules\v1\modules\mobile\components\notifications\NotificationOrder::actionOrder($this->id, $insert);
                    } else {
                        \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCart($this->id, $insert);
                    }
                }
            }

            if (!$this->ediProcessor
                && ($this->status != OrderStatus::STATUS_FORMING && !$insert
                    && (key_exists('total_price', $changedAttributes)
                        || ($this->status == OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED && Yii::$app->params['app_version'] == 2)
                        || ($this->status == OrderStatus::STATUS_DONE && Yii::$app->params['app_version'] == 1)))
                || $this->status == OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR
                || $this->status == OrderStatus::STATUS_CANCELLED) {
                $vendor = $this->vendor;
                $client = $this->client;
                $errorText = Yii::t('app', 'common.models.order.gln', ['ru' => 'Внимание! Выбранный Поставщик работает с Заказами в системе электронного документооборота. Вам необходимо зарегистрироваться в системе EDI и получить GLN-код']);
                $glnArray = $client->getGlnCodes($client->id, $vendor->id);

                if (isset($glnArray['client_gln'])
                    && isset($glnArray['vendor_gln'])
                    && $glnArray['client_gln'] > 0
                    && $glnArray['vendor_gln'] > 0
                ) {
                    $ediIntegration = new EDIIntegration(['orgId' => $vendor->id, 'clientId' => $client->id, 'providerID' => $glnArray['provider_id']]);
                    $result = true;
                    if (($this->status == OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED
                            && Yii::$app->params['app_version'] == 2)
                        || ($this->status == OrderStatus::STATUS_DONE
                            && Yii::$app->params['app_version'] == 1
                            && !$this->is_recadv_sent)) {
                        $result = $ediIntegration->sendOrderInfo($this, true);
                        $this->updateAttributes(['is_recadv_sent' => true]);
                    } elseif ($this->status == OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR || $this->status == OrderStatus::STATUS_CANCELLED) {
                        if ($this->edi_order == null) {
                            $result = $ediIntegration->sendOrderInfo($this, false);
                        } else {
                            if ((isset($changedAttributes['total_price']) && $changedAttributes['total_price'] > 0) || $this->status == OrderStatus::STATUS_CANCELLED) {
                                $result = $ediIntegration->sendOrderInfo($this, false);
                            }
                        }
                    }
                    if (!$result) {
                        Yii::error(Yii::t('app', 'common.models.order.edi_error'));
                    }
                }
                if ((!isset($glnArray['client_gln']) || empty($glnArray['client_gln'])) && isset($glnArray['vendor_gln'])) {
                    throw new BadRequestHttpException($errorText);
                }
            }
            if ($this->status == OrderStatus::STATUS_DONE && !Yii::$app instanceof \yii\console\Application) {
                AutoWaybillHelper::processWaybill($this->id);
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * @return string|null
     */
    public function getEdiOrderDocType()
    {
        $docType = null;
        if ($this->status == OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR) {
            if ($this->edi_order) {
                $docType = Registry::EDI_ORDER_DOCTYPE_EDIT;
            } else {
                $docType = Registry::EDI_ORDER_DOCTYPE_NEW;
            }
        } else {
            $docType = Registry::EDI_ORDER_DOCTYPE_DELETE;
        }
        return $docType;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
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
    public function getUrlForUser($user, $appVersion = 1)
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

            $relationExists = RelationUserOrganization::find()->where(['user_id' => $user->id, 'organization_id' => $this->vendor_id])->exists();
            $token = $user->getJWTToken(Yii::$app->jwt);

            if ($appVersion == 1) {
                if ($user->status == User::STATUS_UNCONFIRMED_EMAIL) {
                    $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                        "/order/view",
                        "id"    => $this->id,
                        "token" => $token, //$user->access_token
                    ]);
                }

                //Если получает вендор, и он не работает в системе, добавляем токен

                if ($relationExists && (($this->vendor->blacklisted == Organization::STATUS_BLACKISTED) || ($this->vendor->blacklisted == Organization::STATUS_UNSORTED))) {
                    $url = Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                        "/order/view",
                        "id"    => $this->id,
                        "token" => $token, //$user->access_token
                    ]);
                }
            } else {
                if ($relationExists) {
                    $url = \Yii::$app->urlManagerFrontend->baseUrl . "/vendor/order/" . $this->id . "?token=" . $token;
                } else {
                    $url = \Yii::$app->urlManagerFrontend->baseUrl . "/client/history/order/" . $this->id;
                }
            }

            return $url;
        }

        //Если пришла модель с дополнительного Емайла
        if ($user instanceof AdditionalEmail) {
            $additonalEmail = AdditionalEmail::findOne(['email' => $user->email]);
            if (($appVersion === 2) && ($additonalEmail->organization->type_id === Organization::TYPE_RESTAURANT)) {
                return \Yii::$app->urlManagerFrontend->baseUrl . "/client/history/order/" . $this->id;
            } else {
                return Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                    "/order/view",
                    "id" => $this->id
                ]);
            }
        }
    }

    /**
     * @param $user
     * @return Organization|null
     */
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(IntegrationInvoice::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceRelation()
    {
        return $this->hasOne(IntegrationInvoice::className(), ['id' => 'invoice_relation']);
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
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
        $query = (new Query())
            ->distinct()
            ->select(['w.id'])
            ->from(Waybill::tableName() . ' as w')
            ->leftJoin(WaybillContent::tableName() . ' as wc', 'wc.waybill_id = w.id')
            ->innerJoin(DBNameHelper::getMainName() . '.' . OrderContent::tableName() . ' as oc', 'oc.id = wc.order_content_id')
            ->where('oc.order_id = :id', [':id' => $this->id]);

        if ($service_id) {
            $query->andWhere('w.service_id = :s_id', [':s_id' => $service_id]);
        }

        $ids = $query->column(\Yii::$app->db_api);

        return Waybill::find()->where(['id' => $ids])->all() ?? [];
    }

    /**
     * Сумма заказа
     *
     * @return string
     */
    public function getTotalPrice()
    {
        $total_price = $this->getAttribute('total_price');
        return number_format(round($total_price, 2), 2, '.', '');
    }

    /**
     * Список позиций в заказе, которые не привязаны к накладным
     *
     * @return array|null
     * @throws \yii\db\Exception
     */
    public function getOrderContentWithOutWaybill()
    {
        $query = new Query();
        $query->select('oc.id');
        $query->from([Order::tableName() . ' o', 'order_content oc']);
        $query->leftJoin(DBNameHelper::getApiName() . '.waybill_content wc', 'wc.order_content_id = oc.id');
        $query->leftJoin(DBNameHelper::getApiName() . '.waybill w', 'w.id = wc.waybill_id');
        $query->where(['o.id' => $this->id]);
        $query->andWhere('oc.order_id = o.id');
        $query->andWhere('w.service_id is null');
        $result = $query->createCommand(\Yii::$app->db)->queryColumn();
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $result = [];
        foreach ($this->orderContent as $orderContent) {
            $result[] = $orderContent->product_id;
        }
        return $result;
    }
}
