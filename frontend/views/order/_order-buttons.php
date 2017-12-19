<?php

use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;

$currencySymbol = $order->currency->symbol;
$statusInfo = '';
$actionButtons = '';
$btnCancel = Html::button('<span><i class="icon fa fa-ban"></i> Отменить</span>', [
            'class' => "btn btn-outline-danger cancel-order",
            'style' => "margin-right: 7px;",
            'data' => [
                'url' => \yii\helpers\Url::to(['order/ajax-cancel-order', 'order_id' => $order->id]),
                'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Отменяем...",
            ],
            'title' => 'Отменить заказ',
        ]);
$btnConfirm = Html::button('<span><i class="icon fa fa-check"></i> Подтвердить</span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Подтверждаем...",
    ],
]);
$btnGetOrder = Html::button('<span><i class="icon fa fa-check"></i> Получить</span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Получаем...",
    ],
]);
$btnCloseOrder = Html::button('<span><i class="icon fa fa-check"></i> Завершить</span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Завершаем...",
    ],
]);
$canEdit = false;
if ($order->isObsolete) {
    $actionButtons .= $btnCancel;
    $actionButtons .= $btnCloseOrder;
    $canEdit = true;
} else {
    switch ($order->status) {
        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_RESTAURANT) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Ожидаем подтверждения</a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case Order::STATUS_PROCESSING:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-processing disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; Исполняется</a>';
            } else {
                $actionButtons .= $btnGetOrder;
            }
            $canEdit = true;
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
?>
<div class="box box-info block_wrapper" style="height:auto;">
    <div class="box-header">
        <?=
        Html::a('<i class="icon fa fa-print"></i> Открыть в виде PDF', ['order/pdf', 'id' => $order->id], [
            'class' => 'btn btn-outline-default pull-right',
            'target' => '_blank',
            'data-toggle' => 'tooltip',
            'title' => 'Открыть PDF с заказом в новом окне'
        ])
        ?>
        <?=
        (isset($canRepeatOrder) && $canRepeatOrder) ? Html::a('<i class="icon fa fa-refresh"></i> Повторить заказ', ['order/repeat', 'id' => $order->id], [
                    'class' => 'btn btn-default pull-right',
                    'style' => 'margin-right: 7px;'
                ]) : ""
        ?>
        <?= $edit ? Html::button('<span><i class="icon fa fa-save"></i> Сохранить</span>', [
            'class' => 'btn btn-success pull-right btnSave', 
            'style' => 'margin-right: 7px;',
            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Сохраняем...",
            ]) : "" ?>
        <?= $canEdit && !$edit ? Html::a('<i class="icon fa fa-save"></i> Редактировать', ['/order/edit', "id" => $order->id], ['class' => 'btn btn-success pull-right btnSave', 'style' => 'margin-right: 7px;']) : "" ?>
    </div>
    <div class="box-body">
        <p class="ppp">Общая сумма</p>

        <p class="pppp"><?= $order->total_price ?> <?= $currencySymbol ?></i></p><br>
        <p class="ps">включая доставку</p>
        <p class="ps"><?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
        <p class="ps">дата создания </p>
        <p class="ps"><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
        <div class="row">
            <div class="col-md-12"><?= $statusInfo ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><?= $actionButtons ?></div>
        </div>
    </div>
</div>