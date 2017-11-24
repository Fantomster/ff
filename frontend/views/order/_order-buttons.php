<?php

use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;

$statusInfo = '';
$actionButtons = '';
$btnCancel = Html::button('<span><i class="icon fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.order.cancel_seven', ['ru'=>'Отменить']) . ' </span>', [
            'class' => "btn btn-outline-danger cancel-order",
            'style' => "margin-right: 7px;",
            'data' => [
                'url' => \yii\helpers\Url::to(['order/ajax-cancel-order', 'order_id' => $order->id]),
                'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.cancel_eight', ['ru'=>'Отменяем...']),
            ],
            'title' => Yii::t('message', 'frontend.views.order.cancel_order', ['ru'=>'Отменить заказ']),
        ]);
$btnConfirm = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.confirm', ['ru'=>'Подтвердить']) . ' </span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.confirming', ['ru'=>'Подтверждаем...']),
    ],
]);
$btnGetOrder = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.receive', ['ru'=>'Получить']) . ' </span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.receiving', ['ru'=>'Получаем...']),
    ],
]);
$btnCloseOrder = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.end_all', ['ru'=>'Завершить']) . ' </span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data' => [
        'action' => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.ending_all', ['ru'=>'Завершаем...']),
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
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.waiting', ['ru'=>'Ожидаем подтверждения']) . ' </a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.waiting_two', ['ru'=>'Ожидаем подтверждения']) . ' </a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case Order::STATUS_PROCESSING:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-processing disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.executes', ['ru'=>'Исполняется']) . ' </a>';
            } else {
                $actionButtons .= $btnGetOrder;
            }
            $canEdit = true;
            break;
        case Order::STATUS_DONE;
            $statusInfo .= '<a href="#" class="btn btn-success disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.ready', ['ru'=>'Выполнен']) . ' </a>';
            break;
        case Order::STATUS_REJECTED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.rejected', ['ru'=>'Отклонен']) . ' </a>';
            break;
        case Order::STATUS_CANCELLED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.canceled_two', ['ru'=>'Отменен']) . ' </a>';
            break;
    }
}
?>
<div class="box box-info block_wrapper" style="height:auto;">
    <div class="box-header">
        <?=
        Html::a('<i class="icon fa fa-print"></i> ' . Yii::t('message', 'frontend.views.order.open_in_pdf', ['ru'=>'Открыть в виде PDF']) . ' ', ['order/pdf', 'id' => $order->id], [
            'class' => 'btn btn-outline-default pull-right',
            'target' => '_blank',
            'data-toggle' => 'tooltip',
            'title' => Yii::t('message', 'frontend.views.order.new_window', ['ru'=>'Открыть PDF с заказом в новом окне'])
        ])
        ?>
        <?=
        (isset($canRepeatOrder) && $canRepeatOrder) ? Html::a('<i class="icon fa fa-refresh"></i> ' . Yii::t('message', 'frontend.views.order.repeat_order_two', ['ru'=>'Повторить заказ']) . ' ', ['order/repeat', 'id' => $order->id], [
                    'class' => 'btn btn-default pull-right',
                    'style' => 'margin-right: 7px;'
                ]) : ""
        ?>
        <?= $edit ? Html::button('<span><i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.save_five', ['ru'=>'Сохранить']) . ' </span>', [
            'class' => 'btn btn-success pull-right btnSave', 
            'style' => 'margin-right: 7px;',
            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.saving_two', ['ru'=>'Сохраняем...']),
            ]) : "" ?>
        <?= $canEdit && !$edit ? Html::a('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.edit', ['ru'=>'Редактировать']), ['/order/edit', "id" => $order->id], ['class' => 'btn btn-success pull-right btnSave', 'style' => 'margin-right: 7px;']) : "" ?>
    </div>
    <div class="box-body">
        <p class="ppp"><?= Yii::t('message', 'frontend.views.order.full_sum', ['ru'=>'Общая сумма']) ?></p>

        <p class="pppp"><?= $order->total_price ?> <i class="fa fa-fw fa-rub" style="font-size: 24px;"></i></p><br>
        <p class="ps"><?= Yii::t('message', 'frontend.views.order.including_delivery', ['ru'=>'включая доставку']) ?></p>
        <p class="ps"><?= $order->calculateDelivery() ?> <i class="fa fa-fw fa-rub"></i></p>
        <p class="ps"><?= Yii::t('message', 'frontend.views.order.creating_date_three', ['ru'=>'дата создания ']) ?></p>
        <p class="ps"><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
        <div class="row">
            <div class="col-md-12"><?= $statusInfo ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><?= $actionButtons ?></div>
        </div>
    </div>
</div>