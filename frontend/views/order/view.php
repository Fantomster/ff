<?php

use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

if (($order->status == Order::STATUS_PROCESSING) && ($organizationType == Organization::TYPE_SUPPLIER)) {
    $quantityEditable = false;
    $priceEditable = false;
} else {
    $quantityEditable = (in_array($order->status, [
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_PROCESSING]));
    $priceEditable = ($organizationType == Organization::TYPE_SUPPLIER) && (in_array($order->status, [
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]));
}
$urlButtons = Url::to(['/order/ajax-refresh-buttons']);
$urlOrderAction = Url::to(['/order/ajax-order-action']);
$urlGetGrid = Url::to(['/order/ajax-order-grid', 'id' => $order->id]);

$js = <<<JS
        $("#chatBody").scrollTop($("#chatBody")[0].scrollHeight);
        $('#actionButtons').on('click', '.btnOrderAction', function() { 
            if ($(this).data("action") == "confirm" && dataEdited) {
                var form = $("#editOrder");
                extData = "&orderAction=confirm"; 
                $("#loader-show").showLoading();
                $.post(
                    form.attr("action"),
                    form.serialize() + extData
                ).done(function(result) {
                    dataEdited = 0;
                    $("#loader-show").hideLoading();
                });
            } else if ($(this).data("action") != "cancel") {
                $("#loader-show").showLoading();
                $.post(
                    "$urlOrderAction",
                        {"action": $(this).data("action"), "order_id": $order->id}
                ).done(function(result) {
                        $('#actionButtons').html(result);
                        //$.pjax.reload({container: "#orderContent"});
                        $("#loader-show").hideLoading();
                });
            }
        });
        $('.content').on('change keyup paste cut', '.view-data', function() {
            dataEdited = 1;
        });
        
        $(document).on("click", "a", function(e) {
            if (dataEdited) {
                e.preventDefault();
                var link = $(this).attr("href");
                if ($(this).data("internal") != 1) {
                    if (link != "#") {
                        swal({
                            title: "Несохраненные изменения!",
                            text: "Вы изменили заказ, но не сохранили изменения!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Уйти",
                            cancelButtonText: "Остаться",
                        }).then(function() {
                            document.location = link;
                        });
                    }
                }
            }
        });
        
        $(document).on('click', '.changed', function() {
            document.location = link;
        });
        $('.content').on('click', '#btnPrint', function(e) {
            e.preventDefault();
            $.get(
                "$urlGetGrid"
            ).done(function(result) {
                $('#orderGrid').html(result);
                $('#toPrint').printThis();
            });
        });
        $('.content').on('click', '#btnSave', function(e) {
            e.preventDefault();
            var form = $("#editOrder");
            $("#loader-show").showLoading();
            $.post(
                form.attr("action"),
                form.serialize()
            ).done(function(result) {
                dataEdited = 0;
                $("#loader-show").hideLoading();
            });
        });
        $('.content').on('click', '.deletePosition', function(e) {
            e.preventDefault();
            target = $(this).data("target");
            $(target).val(0);
            var form = $("#editOrder");
            swal({
                title: "Удаление позиции из заказа",
                text: "Товар будет удален из заказа. Продолжить?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Да, удалить",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve, reject) {
                        $.post(
                            form.attr("action"),
                            form.serialize()
                        ).done(function (result) {
                            if (result) {
                                dataEdited = 0;
                                resolve(result);
                            } else {
                                resolve(false);
                            }
                        });
                    })
                },
            }).then(function() {
                swal({title: "Товар удален из заказа!", type: "success"});
            });        
        });

        $(document).on("click", ".cancel-order", function(e) {
            e.preventDefault();
            var clicked = $(this);
            swal({
                title: "Действительно отменить заказ?",
                input: "textarea",
                inputPlaceholder: "Комментарий",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Нет",
                confirmButtonText: "Да",
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                showLoaderOnConfirm: true,
                preConfirm: function (text) {
                    return new Promise(function (resolve, reject) {
                        $.post(
                            clicked.data("url"),
                            {comment: text}
                        ).done(function (result) {
                            if (result) {
                                resolve(result);
                            } else {
                                resolve(false);
                            }
                        });
                    })
                },
            }).then(function (result) {
                if (result.type == "success") {
                    swal(result);
                } else {
                    swal({title: "Ошибка!", text: "Попробуйте еще раз", type: "error"});
                }
            });
        });
        $(document).on('pjax:complete', function() {
            dataEdited = 0;
        })
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);
\yii2assets\printthis\PrintThisAsset::register($this);

$canRepeatOrder = false;
if ($organizationType == Organization::TYPE_RESTAURANT) {
    switch ($order->status) {
        case Order::STATUS_DONE:
        case Order::STATUS_REJECTED:
        case Order::STATUS_CANCELLED:
            $canRepeatOrder = true;
            break;
    }
}
?>

<section class="content-header">
    <h1>
        <i class="fa fa-history"></i> Заказ №<?= $order->id ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'История заказов',
                'url' => ['order/index'],
            ],
            'Заказ №' . $order->id,
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-8" id="toPrint">
            <div class="box box-info">
                <?php Pjax::begin(['enablePushState' => false, 'id' => 'orderContent', 'timeout' => 30000]); ?>
                <div class="box-header with-border">
                    <h4 class="font-bold">Заказ №<?= $order->id ?></h4><hr>
                    <div class="row m-b-xl" style="line-height: 1.8;">
                        <div class="col-xs-6">
                            <span class="org-type">Заказчик:</span><br>
                            <?= $order->client->name ?><br><br>
                            <address>
                                <b>Город:</b> <?= $order->client->city ?><br>
                                <b>Адрес:</b> <?= $order->client->address ?><br>
                                <b>Телефон:</b> <?= $order->client->phone ?>
                            </address>
                            <p class="text-left">
                                <b>Размещен:</b>
                                <?= $order->createdBy->profile->full_name ?><br>
                                <b>Email:</b> <?= $order->createdBy->email ?>
                            </p>
                            <p class="text-left">
                                <strong>Запрошенная дата доставки:</strong><br>
                                <?= $order->requested_delivery ? Yii::$app->formatter->asDatetime($order->requested_delivery, "php:j M Y") : '' ?>
                            </p>
                        </div>
                        <div class="col-xs-6 text-right">
                            <span class="org-type">Поставщик:</span><br>
                            <?= $order->vendor->name ?><br><br>
                            <address>
                                <b>Город:</b> <?= $order->vendor->city ?><br>
                                <b>Адрес:</b> <?= $order->vendor->address ?><br>
                                <b>Телефон:</b> <?= $order->vendor->phone ?>
                            </address>
                            <p class="text-right">
                                <span><strong>Дата создания заказа:</strong><br><?= Yii::$app->formatter->asDatetime($order->created_at, "php:j M Y") ?></span><br>
                                <span><strong>Дата доставки:</strong><br><?= $order->actual_delivery ? Yii::$app->formatter->asDatetime($order->actual_delivery, "php:j M Y") : '' ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div id="orderGrid">
                        <?php
                        if ($quantityEditable || $priceEditable) {
                            echo $this->render('_edit-grid', compact('dataProvider', 'searchModel', 'quantityEditable', 'priceEditable', 'order', 'canRepeatOrder'));
                        } else {
                            echo $this->render('_view-grid', compact('dataProvider', 'order', 'canRepeatOrder'));
                        }
                        ?>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
                <?php Pjax::end(); ?>
            </div>

        </div>
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Итого</h3>
                    <a href="#" class="btn btn-outline-default pull-right btn-xs" id="btnPrint"><i class="icon fa fa-print"></i> Распечатать</a>
                </div>
                <div class="box-body" id="actionButtons">
                    <?= $this->render('_order-buttons', compact('order', 'organizationType')) ?>   
                </div>
            </div>
            <?php
            echo Html::beginForm(Url::to(['/order/ajax-refresh-buttons']), 'post', ['id' => 'actionButtonsForm']);
            echo Html::hiddenInput('order_id', $order->id);
            echo Html::endForm();
            ?>

        </div>
        <div class="col-md-4">
            <div class="box box-info direct-chat direct-chat-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Чат заказа</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages" id="chatBody">
                        <?php
                        foreach ($order->orderChat as $chat) {
                            echo $this->render('_chat-message', [
                                'id' => $chat->id,
                                'name' => $chat->sentBy->profile->full_name,
                                'sender_id' => $chat->sent_by_id,
                                'message' => $chat->message,
                                'time' => $chat->created_at,
                                'isSystem' => $chat->is_system,
                                'ajax' => 0,
                                'danger' => $chat->danger,
                                'organizationType' => $chat->sentBy->organization->type_id]);
                        }
                        ?>
                    </div>
                    <!--/.direct-chat-messages-->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <?=
                    Html::beginForm(['/order/send-message'], 'POST', [
                        'id' => 'chat-form'
                    ])
                    ?>
                    <div class="input-group">
                        <?= Html::hiddenInput('order_id', $order->id, ['id' => 'order_id']); ?>
                        <?= Html::hiddenInput('sender_id', $user->id, ['id' => 'sender_id']); ?>
                        <?= Html::hiddenInput('', $user->profile->full_name, ['id' => 'name']); ?>
                        <?=
                        Html::textInput('message', null, [
                            'id' => 'message-field',
                            'class' => 'form-control',
                            'placeholder' => 'Сообщение ...'
                        ])
                        ?>                     
                        <span class="input-group-btn">
                            <?=
                            Html::submitButton('<i class="fa fa-paper-plane" style="margin-top:-3px;"></i> Отправить', [
                                'class' => 'btn btn-success'
                            ])
                            ?>
                        </span>
                    </div>
                    <?= Html::endForm() ?>
                </div>
                <!-- /.box-footer-->
            </div>
        </div>
    </div>
</section>
