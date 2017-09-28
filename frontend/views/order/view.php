<?php

use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->title = 'Заказ №' . $order->id;

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
$edit = false;

$js = <<<JS
        $("#chatBody").scrollTop($("#chatBody")[0].scrollHeight);
        
        $('#actionButtons').on('click', '.btnOrderAction', function() { 
            var clickedButton = $(this);
            if ($(this).data("action") == "confirm" && dataEdited) {
                var form = $("#editOrder");
                extData = "&orderAction=confirm"; 
                clickedButton.button("loading");
                $.post(
                    form.attr("action"),
                    form.serialize() + extData
                ).done(function(result) {
                    dataEdited = 0;
                    clickedButton.button("reset");
                });
            } else if ($(this).data("action") != "cancel") {
                clickedButton.button("loading");
                $.post(
                    "$urlOrderAction",
                        {"action": $(this).data("action"), "order_id": $order->id}
                ).done(function(result) {
                        $('#actionButtons').html(result);
                        clickedButton.button("reset");
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
            $(".btnSave").button("loading");
            $.post(
                form.attr("action"),
                form.serialize()
            ).done(function(result) {
                dataEdited = 0;
                $(".btnSave").button("reset");
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
    <div class="container1 ">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <?= $this->render('_bill', compact('order', 'dataProvider')) ?>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6  col-xs-8 pp" id="actionButtons">
                <?= $this->render('_order-buttons', compact('order', 'organizationType', 'canRepeatOrder', 'edit')) ?>   
            </div>
            <?php
            echo Html::beginForm(Url::to(['/order/ajax-refresh-buttons']), 'post', ['id' => 'actionButtonsForm']);
            echo Html::hiddenInput('order_id', $order->id);
            echo Html::endForm();
            ?>
            <div class="col-lg-4 col-md-6  col-sm-6 col-xs-8 pp">
                <div class = "block_wrapper">
                    <div class="block_head_w">
                        <img src="/img/chat.png" alt="">
                    </div>
                    <div class="direct-chat-messages wrapppp" id="chatBody">
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
                                'organizationType' => (isset($chat->sentBy->organization) ? $chat->sentBy->organization->type_id : 1)]);
                        }
                        ?>
                    </div>
                    <?=
                    Html::beginForm(['/order/send-message'], 'POST', [
                        'id' => 'chat-form'
                    ])
                    ?>
                    <div class="block_bot_w">
                        <div class="message-wrap">
                            <?=
                            Html::textInput('message', null, [
                                'id' => 'message-field',
                                'class' => 'message',
                                'placeholder' => 'Отправить сообщение'
                            ])
                            ?>    
                            <button type="submit"><img src="/img/message.png"></button>
                        </div>
                    </div>
                    <?= Html::hiddenInput('order_id', $order->id, ['id' => 'order_id']); ?>
                    <?= Html::hiddenInput('sender_id', $user->id, ['id' => 'sender_id']); ?>
                    <?= Html::hiddenInput('', $user->profile->full_name, ['id' => 'name']); ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</section>
