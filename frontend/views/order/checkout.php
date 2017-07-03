<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use yii\widgets\Breadcrumbs;
use kartik\form\ActiveForm;

$this->registerJs(
        '$("document").ready(function(){
            $(document).on("click", ".remove, .delete, .deleteAll", function(e) {
                e.preventDefault();
                if (!$(".block_wrap_bask_tover").length) {
                    return false;
                }
                clicked = $(this);
                activeCart = $(document).find(".block_wrap_bask_tover.active").attr("id");
                if (clicked.hasClass("remove")) {
                    title = "Удаление товара из корзины";
                    text = "Вы уверены, что хотите удалить товар из заказа?";
                    success = "Товар удален!";
                } else if (clicked.hasClass("delete")){
                    title = "Удаление заказа";
                    text = "Вы уверены, что хотите удалить заказ из корзины?";
                    success = "Заказ удален!";
                } else if (clicked.hasClass("deleteAll")){
                    title = "Очистка корзины";
                    text = "Вы уверены, что хотите удалить все заказы из корзины?";
                    success = "Корзина очищена!";
                    dataEdited = 0;
                }
                swal({
                    title: title,
                    text: text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Да, удалить",
                    cancelButtonText: "Отмена",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                clicked.data("url")
                            ).done(function (result) {
                                if (result) {
                                    resolve(result);
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function() {
                    swal({title: success, type: "success"});
                });
            });

            $(document).on("click", ".create, .createAll", function(e) {
                e.preventDefault();
                if (!$(".block_wrap_bask_tover").length) {
                    return false;
                }
                var clicked = $(this);
                var form = $("#cartForm");
                var extData = "&all=" + clicked.data("all") + "&id=" + clicked.data("id"); 
                if (clicked.hasClass("create")) {
                    title = "Создание заказа";
                    text = "Заказ будет оформлен и направлен поставщику. Продолжить?";
                    success = "Заказ оформлен!";
                } else if (clicked.hasClass("createAll")){
                    title = "Создание заказов";
                    text = "Все заказы из корзины будут оформлены и направлены соответствующим поставщикам. Продолжить?";
                    success = "Все заказы оформлены!";
                }
                swal({
                    title: title,
                    text: text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Да",
                    cancelButtonText: "Отмена",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                clicked.data("url"),
                                form.serialize() + extData
                            ).done(function (result) {
                                if (result) {
                                    resolve(result);
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function() {
                    swal({title: success, type: "success"});
                });
            });

            $(document).on("click", "#saveChanges", function(e) {
                e.preventDefault();
                var clicked = $(this);
                var form = $("#cartForm");
                var extData = "&action=save"; 
                swal({
                    title: "Сохранение изменений",
                    text: "Сохранить изменения в заказах?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Да",
                    cancelButtonText: "Отмена",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                form.attr("action"),
                                form.serialize() + extData
                            ).done(function (result) {
                                if (result) {
                                    $.pjax.reload("#checkout", {url:"http://f-keeper.dev/order/checkout",timeout:30000});
                                    dataEdited = 0;
                                    resolve(result);
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function(result) {
                    swal(result);
                });
            });
            $("#checkout").on("change", ".delivery-date", function(e) {
                $.post(
                    "' . Url::to(['/order/ajax-set-delivery']) . '",
                    {"order_id":$(this).data("order_id"), "delivery_date":$(this).val() }
                ).done(function(result) {
                    if (result) {
                        swal(result);
                    }
                });
            });

            $(document).on("click", ".comment, .add-note", function(e) {
                e.preventDefault();
                var clicked = $(this);
                if (clicked.hasClass("comment")) {
                    title = "Комментарий к заказу";
                } else {
                    title = "Комментарий к товару";
                }
                swal({
                    title: title,
                    input: "textarea",
                    showCancelButton: true,
                    cancelButtonText: "Закрыть",
                    confirmButtonText: "Сохранить",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    showLoaderOnConfirm: true,
                    inputValue: clicked.data("original-title"),
                    onClose: function() {
                        clicked.blur();
                        swal.resetDefaults()
                    },
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
                        clicked.tooltip("hide")
                            .attr("data-original-title", result.comment)
                            .tooltip("fixTitle")
                            .blur();
                        clicked.data("original-title", result.comment);
                        swal(result);
                    } else {
                        swal({title: "Ошибка!", text: "Попробуйте еще раз", type: "error"});
                    }
                });
            });
            
            $(document).on("change keyup paste cut", ".quantity", function() {
                dataEdited = 1;
                $("#saveChanges").show();
            });
            
            $(document).on("click", ".changed", function() {
                document.location = link;
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

            $(document).on("click", ".block_wrap_activess,.active_tov", function() { 
                var block = $(this).parent().parent().parent();
                block.toggleClass("active");
            });

        });'
);
$this->registerCss('
    .date {
        float: right;
        margin-top: 5px;
        margin-right: 10px;
    }
    .delivery-date {
        height: 40px;
        width: 140px !important;
    }
        ');
$this->title = "Корзина";
?>
<section class="content-header">
    <h1>
        <i class="fa fa-shopping-cart"></i> Корзина
        <small>Список готовящихся заказов</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Разместить заказ',
                'url' => ['order/create'],
            ],
            'Корзина',
        ],
    ])
    ?>
</section>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'checkout', 'timeout' => 30000]);
?>
<section class="content">
    <div class="box box-info">
        <div class="box-header checkout-header">
            <div class="row">
                <div class="col-md-6 col-sm-8 col-xs-6">
                    <div class="btn-group" role="group" id="createAll">
                        <?=
                        Html::button('<i class="fa fa-paper-plane" style="margin-top:-3px;"></i><span class="hidden-xs"> Оформить все заказы</span>', [
                            'class' => 'btn btn-success createAll',
                            'data' => [
                                'url' => Url::to(['/order/ajax-make-order']),
                                'all' => true,
                                'id' => null,
                            ]
                        ]);
                        ?>
                        <?=
                        Html::button("&nbsp;<span>$totalCart</span> <i class='fa fa-fw fa-rub'></i>&nbsp;", [
                            'class' => 'btn btn-success createAll btn-outline total-cart',
                            'data' => [
                                'url' => Url::to(['/order/ajax-make-order']),
                                'all' => true,
                                'id' => null,
                            ]
                        ]);
                        ?>
                    </div>
                </div>
                <div class="col-md-6 col-sm-4 col-xs-6">
                    <?=
                    Html::a('<i class="fa fa-ban" style="margin-top:-3px;"></i><span class="hidden-sm hidden-xs"> Очистить корзину</span>', '#', [
                        'class' => 'btn btn-danger pull-right deleteAll',
                        'style' => 'margin-right: 10px; margin-left: 3px;',
                        'data-url' => Url::to(['/order/ajax-delete-order', 'all' => true]),
                    ]);
                    ?>
                    <button class="btn btn-success pull-right" style="display:none;" id="saveChanges"><i class="fa fa-save" style="margin-top:-3px;"></i><span class="hidden-sm hidden-xs"> Сохранить</span></button>
                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="checkout">
                <?php
                $form = ActiveForm::begin([
                            'id' => 'cartForm',
                            'enableAjaxValidation' => false,
                            'options' => [
                                'data-pjax' => true,
                            ],
                            'method' => 'post',
                            'action' => Url::to(['order/checkout']),
                ]);
                ?>
                <?php
                foreach ($orders as $order) {
                    $forMinOrderPrice = $order->forMinOrderPrice();
                    $forFreeDelivery = $order->forFreeDelivery();
                    ?>
                    <div class="block_wrap_bask_tover" id="cartOrder<?= $order->id ?>">
                        <div class="block_left">
                            <div class="block_left_top">

                                <?=
                                Html::a('<img class= "delete_tovar_bask" src="/img/bask_del.png" alt="">', '#', [
                                    'class' => 'delete',
                                    'data-url' => Url::to(['/order/ajax-delete-order', 'all' => false, 'order_id' => $order->id]),
                                ]);
                                ?>
                                <div class="block_wrap_activess">
                                    <p class = "basket_tovar_postav_name">Заказ у поставщика <span><?= $order->vendor->name ?></span></p>
                                    <img class = "active_tov" src="/img/bot_ar.png" alt="">
                                </div>
                                <div style="padding: 20px 0;">
                                    <?=
                                    Html::button('Оформить заказ', [
                                        'class' => 'but_go_zakaz create pull-right',
                                        'data' => [
                                            'url' => Url::to(['/order/ajax-make-order']),
                                            'id' => $order->id,
                                            'all' => false,
                                        ]
                                    ]);
                                    ?>
                                    <?=
                                    Html::button('Комментарий к заказу', [
                                        'class' => 'but_comments comment pull-right',
                                        'data' => [
                                            'url' => Url::to(['order/ajax-set-comment', 'order_id' => $order->id]),
                                            'toggle' => "tooltip",
                                            'placement' => "bottom",
                                            "original-title" => $order->comment,
                                        ]
                                    ]);
                                    ?>
                                    <?=
                                    DatePicker::widget([
                                        'name' => '',
                                        'value' => isset($order->requested_delivery) ? date('d.m.Y', strtotime($order->requested_delivery)) : null,
                                        'options' => [
                                            'placeholder' => 'Дата доставки',
                                            'class' => 'delivery-date',
                                            'data-order_id' => $order->id,
                                        ],
                                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                        'layout' => '{picker}{input}{remove}',
                                        'pluginOptions' => [
                                            'daysOfWeekDisabled' => $order->vendor->getDisabledDeliveryDays(),
                                            'format' => 'dd.mm.yyyy',
                                            'autoclose' => true,
                                            'startDate' => "0d",
                                            'todayHighlight' => true,
                                        ]
                                    ])
                                    ?>
                                </div>
                            </div>
    <?= $this->render('_checkout-content', ['content' => $order->orderContent, 'vendor_id' => $order->vendor_id]) ?>
                        </div>
                        <div class="block_right">
                            <div class="block_right_wrap">
                                <p>Итого: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> р.</p>

                            </div>
                            <div class="block_right_wrap_1">
                                <?php if ($forMinOrderPrice) { ?>
                                    <p>до минимального заказа</p><p><?= $forMinOrderPrice ?> руб</p>
                                <?php } elseif ($forFreeDelivery) { ?>
                                    <p>до бесплатной доставки </p><p><?= $forFreeDelivery ?> руб</p>
                                <?php } else { ?>
                                    <p>бесплатная доставка!</p>
                                <?php } ?>
                                <?=
                                Html::button('Оформить заказ', [
                                    'class' => 'create',
                                    'data' => [
                                        'url' => Url::to(['/order/ajax-make-order']),
                                        'id' => $order->id,
                                        'all' => false,
                                    ]
                                ]);
                                ?>
                            </div>


                        </div>
                    </div>
                <?php } ?>
<?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>
<?php Pjax::end() ?>
