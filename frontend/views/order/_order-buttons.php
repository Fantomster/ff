<?php

use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;

$statusInfo = '';
$actionButtons = '';
$btnCancel = Html::a('<i class="icon fa fa-ban"></i> Отменить', '#', [
            'class' => "btn btn-outline-danger cancel-order",
            'style' => "margin-right: 7px;",
            'data' => [
                'url' => \yii\helpers\Url::to(['order/ajax-cancel-order', 'order_id' => $order->id]),
            ],
            'title' => 'Отменить заказ',
        ]);
if ($order->isObsolete) {
    $actionButtons .= $btnCancel;
    $actionButtons .= '<a href="#" class="btn btn-outline-success btnOrderAction" data-action="confirm"><i class="icon fa fa-check"></i> Завершить</a>';
} else {
    switch ($order->status) {
        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            $actionButtons .= $btnCancel; //'<a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>';
            if ($organizationType == Organization::TYPE_RESTAURANT) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>';
            } else {
                $actionButtons .= '<a href="#" class="btn btn-outline-processing btnOrderAction" data-action="confirm"><i class="icon fa fa-thumbs-o-up"></i> Подтвердить</a>';
            }
            break;
        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
            $actionButtons .= $btnCancel; //'<a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>';
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>';
            } else {
                $actionButtons .= '<a href="#" class="btn btn-outline-processing btnOrderAction" data-action="confirm"><i class="icon fa fa-thumbs-o-up"></i> Подтвердить</a>';
            }
            break;
        case Order::STATUS_PROCESSING:
            $actionButtons .= $btnCancel; //'<a href="#" class="btn btn-outline-danger btnOrderAction" data-action="cancel"><i class="icon fa fa-ban"></i> Отменить</a>';
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-processing disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Исполняется</a>';
            } else {
                $actionButtons .= '<a href="#" class="btn btn-outline-success btnOrderAction" data-action="confirm"><i class="icon fa fa-check"></i> Получить</a>';
            }
            break;
        case Order::STATUS_DONE;
            $statusInfo .= '<a href="#" class="btn btn-success disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Выполнен</a>';
            break;
        case Order::STATUS_REJECTED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Отклонен</a>';
            break;
        case Order::STATUS_CANCELLED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Отменен</a>';
            break;
    }
}
//$actionButtons .= '<a href="#" class="btn btn-outline-default" id="btnPrint"><i class="icon fa fa-print"></i> Распечатать</a>';
?>
<div class="box box-info">
    <div class="box-header">
        <h3 class="box-title">Итого</h3>
        <?php //<a href="#" class="btn btn-outline-default pull-right btn-xs" id="btnPrint"><i class="icon fa fa-print"></i> Распечатать</a> ?>
        <?=
        Html::a('<i class="icon fa fa-print"></i> Открыть в виде PDF', ['order/pdf', 'id' => $order->id], [
            'class' => 'btn btn-outline-default pull-right btn-xs',
            'target' => '_blank',
            'data-toggle' => 'tooltip',
            'title' => 'Открыть PDF с заказом в новом окне'
        ])
        ?>
    </div>
    <div class="box-body">
        <p class="text-left m-b-sm"><b>Дата создания заказа:</b><br>
            <?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
        <p class="text-left m-b-sm"><b>Стоимость доставки:</b><br>
            <?= $order->vendor->delivery->delivery_charge ?> руб</p>
        <p class="text-left m-b-sm"><b>Стоимость заказа:</b><br>
            <?= $order->total_price ?> руб</p>
        <div class="row">
            <div class="col-md-12"><?= $statusInfo ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><?= $actionButtons ?></div>
        </div>
    </div>
</div>