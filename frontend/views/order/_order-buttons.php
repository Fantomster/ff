<?php

use common\models\Order;
use common\models\OrderStatus;
use common\models\Organization;
use yii\helpers\Html;

$url = \yii\helpers\Url::to(['order/ajax-order-update-waybill']);

$this->registerJs('
        $(document).on("click", ".completeEdi", function(e) {
            e.preventDefault();
            clicked = $(this);
                var title = "' . Yii::t('app', 'frontend.views.order.complete_edi', ['ru' => 'Внимание, данные о фактическом приеме товара будут направлены ПОСТАВЩИКУ! Вы подтверждаете, корректность данных?']) . ' ";
            swal({
                title: title,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep', ['ru' => 'Да']) . ' ",
                cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel', ['ru' => 'Отмена']) . ' ",
                showLoaderOnConfirm: true,
            }).then(function(result) {
                if (result.dismiss === "cancel") {
                    swal.close();
                } else {
                    document.location = clicked.data("url")
                }
            });
        });
        
        $(document).on("click", "#alWaybillNumber", function(e) {
            e.preventDefault();
            var clicked = $(this);
            var title = "' . Yii::t('app', 'Номер накладной') . ' ";
            var waybillNumber = $("#alHiddenWaybillNumber").val();
            swal({
                title: title,
                showCancelButton: true,
                html:"<input type=text id=swal-input1 value=\"" + waybillNumber + "\" class=swal2-input>",
                confirmButtonText: "' . Yii::t('app', 'Сохранить') . ' ",
                cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel', ['ru' => 'Отмена']) . ' ",
                preConfirm: function() {
                    var len = $(\'#swal-input1\').val().length;
                    if (len > 32) {
                       swal.showValidationError(\'Номер накладной не может превышать 32 символов!\')
                    }
                }
        }).then(function(result) {
                if (result.dismiss === "cancel") {
                    swal.close();
                } else {
                    $("#alWaybillNumber").prop("disabled", "disabled");
                    var val = $("#swal-input1").val();
                    $.ajax({
                        url: "' . $url . '",
                        "data": { "waybill_number": val, "order_id": ' . $order->id . ' },
                        "type": "POST",
                        "cache": false,
                        "success": function () {
                           if(val == ""){
                                val = "' . Yii::t('app', 'common.config.main.empty', ['ru' => 'пусто']) . '"
                            }
                            $("#alHiddenWaybillNumber").val(val);
                        },
                        "error": function () {
                            alert( "Error detected when sending table data to server" );
                        }
			        });
			        setTimeout(function(){
			            $("#alWaybillNumber").prop("disabled", false);
			        }, 3000);
                }
            });
        });
');

$currencySymbol = $order->currency->symbol;
$statusInfo = '';
$actionButtons = '';
$disabledString = (Yii::$app->user->identity->role_id == \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR) ? " disabled" : "";
$btnCancel = Html::button('<span><i class="icon fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.order.cancel_seven', ['ru' => 'Отменить']) . ' </span>', [
    'class' => "btn btn-outline-danger cancel-order$disabledString",
    'style' => "margin-right: 7px;",
    'data'  => [
        'url'          => \yii\helpers\Url::to(['order/ajax-cancel-order', 'order_id' => $order->id]),
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.cancel_eight', ['ru' => 'Отменяем...']),
    ],
    'title' => Yii::t('message', 'frontend.views.order.cancel_order', ['ru' => 'Отменить заказ']),
]);
$btnConfirm = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.confirm', ['ru' => 'Подтвердить']) . ' </span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data'  => [
        'action'       => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.confirming', ['ru' => 'Подтверждаем...']),
    ],
]);
$btnGetOrder = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.receive', ['ru' => 'Получить']) . ' </span>', [
    'class' => "btn btn-outline-success btnOrderAction",
    'data'  => [
        'action'       => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.receiving', ['ru' => 'Получаем...']),
    ],
]);
if (isset($order->vendor->ediOrganization->gln_code) && $order->vendor->ediOrganization->gln_code > 0) {
    $data = [
        'toggle'         => 'tooltip',
        'original-title' => Yii::t('message', 'frontend.views.order.complete_order', ['ru' => 'Завершить заказ']),
        'url'            => \yii\helpers\Url::to(['order/complete-obsolete', 'id' => $order->id])
    ];
} else {
    $data = [
        'action'       => "confirm",
        'loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.ending_all', ['ru' => 'Завершаем...']),
    ];
}
$btnCloseOrder = Html::button('<span><i class="icon fa fa-check"></i> ' . Yii::t('message', 'frontend.views.order.end_all', ['ru' => 'Завершить']) . ' </span>', [
    'class' => (isset($order->vendor->ediOrganization->gln_code) && $order->vendor->ediOrganization->gln_code > 0) ? 'btn btn-outline-success completeEdi' : 'btn btn-outline-success btnOrderAction',
    'data'  => $data,
]);
$canEdit = false;
if ($order->isObsolete) {
    if (!isset($order->vendor->ediOrganization->gln_code)) {
        $actionButtons .= $btnCancel;
        $canEdit = true;
    }
    $actionButtons .= $btnCloseOrder;
} else {
    switch ($order->status) {
        case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_RESTAURANT) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.waiting', ['ru' => 'Ожидаем подтверждения']) . ' </a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
            $actionButtons .= $btnCancel;
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-warning disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.waiting_two', ['ru' => 'Ожидаем подтверждения']) . ' </a>';
            } else {
                $actionButtons .= $btnConfirm;
            }
            $canEdit = true;
            break;
        case OrderStatus::STATUS_PROCESSING:
            if (!isset($data->vendor->ediOrganization->gln_code)) {
                $actionButtons .= $btnCancel;
            }
            if ($organizationType == Organization::TYPE_SUPPLIER) {
                $statusInfo .= '<a href="#" class="btn btn-processing disabled status"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.executes', ['ru' => 'Исполняется']) . ' </a>';
            } else {
                $actionButtons .= $btnGetOrder;
            }
            $canEdit = true;
            break;
        case OrderStatus::STATUS_DONE;
            $statusInfo .= '<a href="#" class="btn btn-success disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.ready', ['ru' => 'Выполнен']) . ' </a>';
            $canEdit = true;
            break;
        case OrderStatus::STATUS_REJECTED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.rejected', ['ru' => 'Отклонен']) . ' </a>';
            break;
        case OrderStatus::STATUS_CANCELLED;
            $statusInfo .= '<a href="#" class="btn btn-danger disabled"><span class="badge"><i class="icon fa fa-info"></i></span>&nbsp; ' . Yii::t('message', 'frontend.views.order.canceled_two', ['ru' => 'Отменен']) . ' </a>';
            break;
    }
}
//if($organizationType == Organization::TYPE_RESTAURANT || $organizationType == Organization::TYPE_FRANCHISEE){
//    $canEdit = true;
//}
//if(isset($order->vendor->ediOrganization->gln_code) && $order->vendor->ediOrganization->gln_code > 0 && $order->status!=Order::STATUS_DONE){
//    $canEdit = false;
//}else{
//    $canEdit = true;
//}
?>
<div class="box box-info block_wrapper" style="height:auto;">
    <div class="box-header">
        <?=
        Html::a('<i class="icon fa fa-print"></i> ' . Yii::t('message', 'frontend.views.order.open_in_pdf', ['ru' => 'Открыть в виде PDF']) . ' ', ['order/pdf', 'id' => $order->id], [
            'class'       => 'btn btn-outline-default pull-right',
            'target'      => '_blank',
            'data-toggle' => 'tooltip',
            'title'       => Yii::t('message', 'frontend.views.order.new_window', ['ru' => 'Открыть PDF с заказом в новом окне'])
        ])
        ?>
        <?=
        (isset($canRepeatOrder) && $canRepeatOrder) ? Html::a('<i class="icon fa fa-refresh"></i> ' . Yii::t('message', 'frontend.views.order.repeat_order_two', ['ru' => 'Повторить заказ']) . ' ', ['order/repeat', 'id' => $order->id], [
            'class' => 'btn btn-default pull-right',
            'style' => 'margin-right: 7px;'
        ]) : ""
        ?>
        <?=
        $edit ? Html::button('<span><i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.save_five', ['ru' => 'Сохранить']) . ' </span>', [
            'class'             => 'btn btn-success pull-right btnSave',
            'style'             => 'margin-right: 7px;',
            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.saving_two', ['ru' => 'Сохраняем...']),
        ]) : ""
        ?>
        <?= $canEdit && !$edit ? Html::a('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.edit', ['ru' => 'Редактировать']), ['/order/edit', "id" => $order->id], ['class' => 'btn btn-success pull-right btnSave', 'style' => 'margin-right: 7px;']) : "" ?>
        <div style="clear: both; height: 5px;"></div>
        <?php
        $user = Yii::$app->user->identity;
        $licenses = $user->organization->getLicenseList();
        if (isset($licenses['mercury']))
            echo Html::a(' ' . Yii::t('app', 'frontend.views.order.index.mercury', ['ru' => 'Погасить ВСД']) . ' ', ['/clientintegr/merc/default'], [
                'class'       => 'btn btn-outline-processing pull-right',
                'target'      => '',
                'data-toggle' => 'tooltip',
                'title'       => Yii::t('app', 'frontend.views.order.index.mercury', ['ru' => 'Погасить ВСД'])
            ]);
        ?>

        <?=
        Html::a('<i class="icon fa fa-file-excel-o"></i> ' . Yii::t('app', 'frontend.views.order.index.report', ['ru' => 'отчет xls']) . ' ', ['order/order-to-xls', 'id' => $order->id], [
            'class'       => 'btn btn-outline-success pull-right',
            'target'      => '',
            'data-toggle' => 'tooltip',
            'title'       => Yii::t('app', 'frontend.views.order.index.report', ['ru' => 'отчет xls'])
        ])
        ?>

    </div>
    <div class="box-body">
        <p class="ppp"><?= Yii::t('message', 'frontend.views.order.full_sum', ['ru' => 'Общая сумма']) ?></p>

        <p class="pppp"><?= $order->total_price ?> <?= $currencySymbol ?></i></p><br>
        <p class="ps"><?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('app', 'Номер накладной'), ['class' => 'btn btn-success btnWaybillNumber', 'id' => 'alWaybillNumber']) ?>
            <input type="hidden"
                   value="<?= ($order->waybill_number != null && $order->waybill_number != '') ? $order->waybill_number : Yii::t('app', 'common.config.main.empty', ['ru' => 'пусто']) ?>"
                   id="alHiddenWaybillNumber"></p>
        <p class="ps"><?= Yii::t('message', 'frontend.views.order.including_delivery', ['ru' => 'включая доставку']) ?></p>
        <p class="ps"><?= $order->calculateDelivery() ?> <?= $currencySymbol ?></p>
        <p class="ps"><?= Yii::t('message', 'frontend.views.order.creating_date_three', ['ru' => 'дата создания ']) ?></p>
        <p class="ps"><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></p>
        <div class="row">
            <div class="col-md-12"><?= $statusInfo ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><?= $actionButtons ?></div>
        </div>
    </div>
</div>