<?php

use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;
use dosamigos\fileupload\FileUploadUI;

$this->title = Yii::t('message', 'frontend.views.order.order_edit', ['ru' => 'Редактирование заказа №']) . $order->id;

if (($order->status == Order::STATUS_PROCESSING) && ($organizationType == Organization::TYPE_SUPPLIER)) {
    $quantityEditable = false;
    $priceEditable = false;
} else {
    $quantityEditable = (in_array($order->status, [
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_PROCESSING]));
    $priceEditable = (in_array($order->status, [
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]));
}
if ($organizationType == Organization::TYPE_RESTAURANT || $organizationType == Organization::TYPE_FRANCHISEE) {
    $quantityEditable = true;
    $priceEditable = true;
}
$urlButtons = Url::to(['/order/ajax-refresh-buttons']);
$urlOrderAction = Url::to(['/order/ajax-order-action']);
$urlGetGrid = Url::to(['/order/ajax-order-grid', 'id' => $order->id]);
$urlViewOrder = Url::to(['/order/view', 'id' => $order->id]);
$edit = true;
$refreshUrl = Url::to(['/order/edit', "id" => $order->id]);

$arr = [
    Yii::t('message', 'frontend.views.order.var1', ['ru' => 'Несохраненные изменения!']),
    Yii::t('message', 'frontend.views.order.var2', ['ru' => 'Вы изменили заказ, но не сохранили изменения!']),
    Yii::t('message', 'frontend.views.order.var3', ['ru' => 'Уйти']),
    Yii::t('message', 'frontend.views.order.var4', ['ru' => 'Остаться']),
    Yii::t('message', 'frontend.views.order.var5', ['ru' => 'Удаление позиции из заказа']),
    Yii::t('message', 'frontend.views.order.var6', ['ru' => 'Товар будет удален из заказа. Продолжить?']),
    Yii::t('message', 'frontend.views.order.var7', ['ru' => 'Да, удалить']),
    Yii::t('message', 'frontend.views.order.var8', ['ru' => 'Отмена']),
    Yii::t('message', 'frontend.views.order.var9', ['ru' => 'Товар удален из заказа!']),
    Yii::t('message', 'frontend.views.order.var10', ['ru' => 'Действительно отменить заказ?']),
    Yii::t('message', 'frontend.views.order.var11', ['ru' => 'Комментарий']),
    Yii::t('message', 'frontend.views.order.var12', ['ru' => 'Нет']),
    Yii::t('message', 'frontend.views.order.var13', ['ru' => 'Да']),
    Yii::t('message', 'frontend.views.order.var14', ['ru' => 'Ошибка!']),
    Yii::t('message', 'frontend.views.order.var15', ['ru' => 'Попробуйте еще раз']),
];

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
                            title: "$arr[0]",
                            text: "$arr[1]",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonText: "$arr[2]",
                            cancelButtonText: "$arr[3]",
                        }).then(function(result) {
                            if (result.dismiss === "cancel") {
                               swal.close();
                            } else {
                                document.location = link;
                            }
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
        $('.content').on('click', '.btnSave', function(e) {
            e.preventDefault();
            var form = $("#editOrder");
            $(".btnSave").button("loading");
            form.submit();
            saving = true;
        });
        $('.content').on('click', '.deletePosition', function(e) {
            e.preventDefault();
            target = $(this).data("target");
//            var form = $("#editOrder");
            swal({
                title: "$arr[4]",
                text: "$arr[5]",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "$arr[6]",
                cancelButtonText: "$arr[7]"
            }).then(function(result) {
                if (result.dismiss === "cancel") {
                    swal.close();
                } else {
                    $(target).val(0);
                    $(target).closest('tr').hide();
                    swal({title: "$arr[8]", type: "success"});
                }
            });        
        });

        $(document).on("click", ".cancel-order", function(e) {
            e.preventDefault();
            var clicked = $(this);
            swal({
                title: "$arr[9]",
                input: "textarea",
                inputPlaceholder: "$arr[10]",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "$arr[11]",
                confirmButtonText: "$arr[12]",
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
                if (result.value.type == "success") {
                    swal(result.value);
                } else if (result.dismiss == "cancel") {
                    swal.close();
                } else {
                    swal({title: "$arr[13]", text: "$arr[14]", type: "error"});
                }
            });
        });
        $(document).on('pjax:complete', function() {
            dataEdited = 0;
        })
        $(document).on("change paste keyup", ".quantityAdd", function() {
        var btnAddToCart = $(this).parent().parent().parent().find(".add-to-cart");
        if ($(this).val() > 0) {
            btnAddToCart.removeClass("disabled");
        } else {
            btnAddToCart.addClass("disabled");
        }
    });
        $(document).on("hidden.bs.modal", "#showProducts", function() {
        $(this).data("bs.modal", null);
        $(".modal-header").html("<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span>");
        $(".modal-body").html("");
        window.location.replace(window.location.protocol + "//" + window.location.host + "$refreshUrl");
    });
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);
\common\assets\PrintThisAsset::register($this);

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
        <i class="fa fa-history"></i> <?= Yii::t('message', 'frontend.views.order.order_five', ['ru' => 'Заказ №']) ?><?= $order->id ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.order.orders_history', ['ru' => 'История заказов']),
                'url' => ['order/index'],
            ],
            Yii::t('message', 'frontend.views.order.order_number', ['ru' => 'Заказ №']) . $order->id,
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-8" id="toPrint">
            <div class="box box-info">
                <div class="box-header">
                    <h4 class="font-bold"><?= Yii::t('message', 'frontend.views.order.order_six', ['ru' => 'Заказ']) ?> №<?= $order->id ?></h4><hr>
                    <?=
                    (($order->status != Order::STATUS_CANCELLED && $order->status != Order::STATUS_REJECTED && !isset($order->vendor->ediOrganization->gln_code)) || ($order->status == Order::STATUS_DONE && isset($order->vendor->ediOrganization->gln_code))) ?
                            Html::a('<span><i class="icon fa fa-plus"></i> ' . Yii::t('message', 'frontend.views.order.add_to_order', ['ru' => 'Добавить в заказ']) . ' </span>', Url::to(['order/ajax-show-products', 'order_id' => $order->id]), [
                                'class' => 'btn btn-success pull-right btnAdd',
                                'data' => [
                                    'target' => '#showProducts',
                                    'toggle' => 'modal',
                                    'backdrop' => 'static',
                                ],
                                'title' => Yii::t('message', 'frontend.views.order.add_to_order', ['ru' => 'Добавить в заказ']),
                            ]) : ""
                    ?>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div id="orderGrid">
                        <?php
                        echo $this->render('_edit-grid', compact('dataProvider', 'searchModel', 'quantityEditable', 'priceEditable', 'order', 'canRepeatOrder'));
                        ?>
                    </div>
                    <div style="display: block;">
                        <?= ''
//                        FileUploadUI::widget([
//                            //'model' => $model,
//                            'name' => 'testtest',
//                            'attribute' => 'image',
//                            'url' => ['media/upload', 'id' => 1],
//                            'gallery' => false,
//                            'fieldOptions' => [
//                                'accept' => 'image/*'
//                            ],
//                            'clientOptions' => [
//                                'maxFileSize' => 2000000
//                            ],
//                            // ...
//                            'clientEvents' => [
//                                'fileuploaddone' => 'function(e, data) {
//                                console.log(e);
//                                console.log(data);
//                            }',
//                                'fileuploadfail' => 'function(e, data) {
//                                console.log(e);
//                                console.log(data);
//                            }',
//                            ],
//                        ]);
                        ?>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
<?php //Pjax::end();  ?>
            </div>

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
                            'placeholder' => Yii::t('message', 'frontend.views.order.send_message_two', ['ru' => 'Отправить сообщение'])
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
</section>

<?=
Modal::widget([
    'id' => 'showProducts',
    'clientOptions' => false,
    'size' => Modal::SIZE_LARGE,
    'header' => '<span class=\'glyphicon-left glyphicon glyphicon-refresh spinning\'></span>',
])
?>
