<?php

namespace common\models;

use api_web\components\Registry;
use yii\db\ActiveRecord;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "order_status".
 *
 * @property int    $id          Идентификатор записи в таблице
 * @property string $denom       Наименование константы статуса заказа
 * @property string $comment     Псевдоним наименования статуса заказа (source_message)
 * @property string $comment_edi Комментарий к статусу заказа, связанного с EDI
 */
class OrderStatus extends ActiveRecord
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

    static $clientPermissionsDef = [
        'edit'     => true,
        'cancel'   => true,
        'complete' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_status}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['denom', 'comment', 'comment_edi'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'          => Yii::t('app', 'common.models.order_status.id', ['ru' => 'ID статуса заказа']),
            'denom'       => Yii::t('app', 'common.models.order_status.denom', ['ru' => 'ИДЕНТИФИКАТОР СООТВЕТСТВУЮЩЕЙ КОНСТАНТЫ В МОДЕЛИ ORDER']),
            'comment'     => Yii::t('app', 'common.models.order_status.comment', ['ru' => 'Общее описание статуса']),
            'comment_edi' => Yii::t('app', 'common.models.order_status.comment_edi', ['ru' => 'Описание статуса заказа, обрабатываемого в системе EDI']),
        ];
    }

    /**
     * Get permissions for a client that is "the owner of the order" by the status of an order
     *
     * @param int $status Status of an order (Order->status_id)
     * @return array?
     */
    public static function getClientPermissions(int $status = null): ?array
    {
        $res = self::$clientPermissionsDef;
        if (!$status || !in_array($status, [
                self::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                self::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                self::STATUS_PROCESSING,
                self::STATUS_DONE,
                self::STATUS_REJECTED,
                self::STATUS_CANCELLED,
                self::STATUS_FORMING,
                self::STATUS_EDI_SENT_BY_VENDOR,
                self::STATUS_EDI_ACCEPTANCE_FINISHED,
            ])) {
            return null;
        }
        if (in_array($status, [
            self::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            self::STATUS_PROCESSING,
            self::STATUS_EDI_SENT_BY_VENDOR,
            self::STATUS_EDI_ACCEPTANCE_FINISHED,
            self::STATUS_DONE,
            self::STATUS_CANCELLED,
        ])) {
            $res = [
                'edit'     => false,
                'cancel'   => false,
                'complete' => false,
            ];
            if ($status == self::STATUS_EDI_SENT_BY_VENDOR) {
                $res['edit'] = $res['complete'] = true;
            }
        }
        return $res;
    }

    public static function getStatusesArrayForBackend()
    {
        return [
            self::STATUS_PROCESSING,
            self::STATUS_DONE,
            self::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            self::STATUS_AWAITING_ACCEPT_FROM_VENDOR
        ];
    }

    /**
     * Get a custom permission for a client that is "the owner of the order" by the status of an order
     *
     * @param int    $status Status of an order (Order->status_id)
     * @param string $type   Type of client permission
     * @return bool?
     */
    public static function getClientPermissionByType(int $status = null, string $type = null): ?bool
    {
        if (!$status || ($type && !array_key_exists($type, self::$clientPermissionsDef))) {
            return null;
        }
        $statuses = self::getClientPermissions($status);
        if (!$statuses) {
            return null;
        }
        if ($statuses[$type]) {
            return true;
        }
        return false;
    }

    /**
     * CheckOrderPermission
     *
     * @param Order  $order               Order
     * @param string $type                Type of client permission
     * @param array  $ediExcludesStatuses Excluded statuses for this check
     * @throws BadRequestHttpException
     */
    public static function checkEdiOrderPermissions(Order $order = null, string $type = null, array $ediExcludesStatuses = [])
    {
        if ($order && $order->service_id == Registry::EDI_SERVICE_ID) {
            if (!$ediExcludesStatuses || !in_array($order->status, $ediExcludesStatuses)) {
                if (OrderStatus::getClientPermissionByType($order->status, $type) == null) {
                    throw new BadRequestHttpException('Bad permission type! Check the awailable list of types.');
                } elseif (!OrderStatus::getClientPermissionByType($order->status, $type)) {
                    throw new BadRequestHttpException('Current user has no permissions for this transaction. Operation is blocked!');
                }
            }
        }
    }
}