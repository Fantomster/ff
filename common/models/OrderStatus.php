<?php

namespace common\models;

use yii\db\ActiveRecord;
use \Yii;

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
    const STATUS_NEW = 10;

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
