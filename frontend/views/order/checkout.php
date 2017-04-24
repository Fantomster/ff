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
                clicked = $(this);
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
                                    $.pjax.reload({container: "#checkout"});
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
            
        });'
);
$this->title = "Корзина";
?>
<section class="content-header">
    <h1>
        <i class="fa fa-shopping-cart"></i></i> Корзина
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
                <?php foreach ($orders as $order) { ?>
                    <div class="box box-info box-order-content">
                        <div class="box-header with-border">
                            <div class="row">
                                <div class="col-md-8 col-sm-8 col-xs-8">
                                    <h3 class="box-title">Заказ у <?= $order->vendor->name ?> на сумму <span id="orderTotal<?= $order->id ?>" class="text-success"><?= $order->total_price ?></span><i class="fa fa-fw fa-rub text-success"></i></h3>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <div class="pull-right">
                                        <?= 
                                            Html::a('<i class="fa fa-close m-r-xxs" style="margin-top:-2px;"></i>', '#', [
                                                'class' => 'btn btn-outline btn-xs btn-outline-danger delete',
                                                'style' => 'margin-right:10px;',
                                                'data-url' => Url::to(['/order/ajax-delete-order', 'all' => false, 'order_id' => $order->id]),
                                            ]);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="panel-group">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="form-inline">
                                            <div class="row">
                                                <div class="col-md-4 col-sm-6 col-xs-6">
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
                                                <div class="col-md-8 col-sm-6 col-xs-6">
                                                    <?= 
                                                        Html::a('<i class="fa fa-paper-plane" style="margin-top:-3px;"></i><span class="hidden-fk"> Оформить заказ</span>', '#', [
                                                            'class' => 'btn btn-success create pull-right',
                                                            'data' => [
                                                                'url' => Url::to(['/order/ajax-make-order']),
                                                                'id' => $order->id,
                                                                'all' => false,
                                                            ]
                                                        ]);
                                                    ?>
                                                    <a class="btn btn-gray comment pull-right"
                                                       data-url="<?= Url::to(['order/ajax-set-comment', 'order_id' => $order->id]) ?>"
                                                       data-toggle="tooltip" 
                                                       data-placement="bottom" 
                                                       data-original-title="<?= $order->comment ?>"
                                                       href="#">
                                                        <i class="fa fa-comment" style="margin-top:-3px;"></i><span class="hidden-fk"> Комментарий к заказу</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body cart-order" id="order<?= $order->id ?>">
                                        <?= $this->render('_checkout-content', ['content' => $order->orderContent, 'vendor_id' => $order->vendor_id]) ?>
                                    </div>
                                </div>
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