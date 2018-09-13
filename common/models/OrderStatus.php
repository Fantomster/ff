<?php

namespace common\models;

use yii\db\ActiveRecord;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "order_status".
 * @property integer $id ID статуса заказа
 * @property string $denom ИДЕНТИФИКАТОР СООТВЕТСТВУЮЩЕЙ КОНСТАНТЫ В МОДЕЛИ ORDER
 * @property string $comment Общее описание статуса
 * @property string $comment_edo Описание статуса заказа, обрабатываемого в системе EDI
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
    const STATUS_EDO_SENT_BY_VENDOR = 8;
    const STATUS_EDO_ACCEPTANCE_FINISHED = 9;

    const YES = 'Y';
    const NO = 'N';

    static $clientPermissionsDef = [
        'edit' => true,
        'cancel' => true,
        'complete' => true,
    ];

    /**
     * Get permissions for a client that is "the owner of the order" by the status of an order
     * @param int $status Status of an order (Order->status_id)
     * @return array?
     */
    public static function getClientPermissions(int $status = null): ?array
    {
        $res = self::$clientPermissionsDef;
        if (!$status || !in_array($status, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
            return null;
        }
        if (in_array($status, [1, 3, 8, 9, 4, 6])) {
            $res = [
                'edit' => false,
                'cancel' => false,
                'complete' => false,
            ];
            if ($status == 8) {
                $res['edit'] = $res['complete'] = true;
            }
        }
        return $res;
    }

    /**
     * Get a custom permission for a client that is "the owner of the order" by the status of an order
     * @param int $status Status of an order (Order->status_id)
     * @param string $type Type of client permission
     * @return string?
     */
    public static function getClientPermissionByType(int $status = null, string $type = NULL): ?string
    {
        if (!$status || ($type && !array_key_exists($type, self::$clientPermissionsDef))) {
            return null;
        }
        $statuses = self::getClientPermissions($status);
        if (!$statuses) {
            return null;
        }
        if ($statuses[$type]) {
            return self::YES;
        }
        return self::NO;
    }

    /**
     * CheckOrderPermission
     * @param Order $order Order
     * @param string $type Type of client permission
     * @param array $edoExcludesStatuses Excluded statuses for this check
     * @throws BadRequestHttpException
     */
    public static function checkEdiOrderPermissions(Order $order = null, string $type = null, array $edoExcludesStatuses = [])
    {
        if ($order && $order->service_id == (AllService::findOne(['denom' => 'EDI']))->id) {
            if (!$edoExcludesStatuses || !in_array($order->status, $edoExcludesStatuses)) {
                if (OrderStatus::getClientPermissionByType($order->status, $type) != self::YES) {
                    throw new BadRequestHttpException('Current user has no permissions for this transaction. Operation is blocked!');
                }
            }
        }
    }

    public static function tableName()
    {
        return 'order_status';
    }

    public function rules(): array
    {
        return [
            [['id'], 'required'],
            [['denom', 'comment', 'comment_edo'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'common.models.order_status.id', ['ru' => 'ID статуса заказа']),
            'denom' => Yii::t('app', 'common.models.order_status.denom', ['ru' => 'ИДЕНТИФИКАТОР СООТВЕТСТВУЮЩЕЙ КОНСТАНТЫ В МОДЕЛИ ORDER']),
            'comment' => Yii::t('app', 'common.models.order_status.comment', ['ru' => 'Общее описание статуса']),
            'comment_edo' => Yii::t('app', 'common.models.order_status.comment_edo', ['ru' => 'Описание статуса заказа, обрабатываемого в системе EDI']),
        ];
    }

}