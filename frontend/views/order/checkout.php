<?php

use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use yii\widgets\Breadcrumbs;

//kartik\growl\GrowlAsset::register($this);

$this->registerJs(
        '$("document").ready(function(){
            $("#checkout").on("click", ".create", function(e) {
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-make-order']) . '",
                    {"id": $(this).data("id"), "all": 0 }
                ).done(function(result) {
                    if (result) {
                        //$.pjax.reload({container: "#checkout"});
                        $.notify(result.growl.options, result.growl.settings);
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#checkout").on("click", ".delete", function(e) {
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-delete-order']) . '",
                    {"id": $(this).data("id"), "all":0 }
                ).done(function(result) {
                    if (result) {
                        //$.pjax.reload({container: "#checkout"});
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#checkout").on("click", "#deleteAll", function(e) {
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-delete-order']) . '",
                    {"all":1 }
                ).done(function(result) {
                    if (result) {
                        //$.pjax.reload({container: "#checkout"});
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#checkout").on("click", "#createAll", function(e) {
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-make-order']) . '",
                    {"all":1 }
                ).done(function(result) {
                    if (result) {
                        //$.pjax.reload({container: "#checkout"});
                        $.notify(result.growl.options, result.growl.settings);
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#checkout").on("click", ".remove", function(e) {
            e.preventDefault();
                $("#loader-show").showLoading();
                $.post(
                    "' . Url::to(['/order/ajax-remove-position']) . '",
                    {"product_id": $(this).data("product_id"), "vendor_id": $(this).data("vendor_id")}
                ).done(function(result) {
                    if (result) {
                        //$.pjax.reload({container: "#checkout"});
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#checkout").on("change", ".delivery-date", function(e) {
                $.post(
                    "' . Url::to(['/order/ajax-set-delivery']) . '",
                    {"order_id":$(this).data("order_id"), "delivery_date":$(this).val() }
                ).done(function(result) {
                    if (result) {
                        $.notify(result.growl.options, result.growl.settings);
                    }
                });
            });
            $("body").on("hidden.bs.modal", "#changeComment, #changeNote", function() {
                $(this).data("bs.modal", null);
            });
            $("body").on("submit", "#commentForm", function() {
                return false;
            });
            $("#changeComment").on("click", ".saveComment", function() {
                $("#loader-show").showLoading();
                var form = $("#commentForm");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function (result) {
                    if (result) {
                        $.notify(result.growl.options, result.growl.settings);
                    }
                    $("#loader-show").hideLoading();
                });
            });
            $("#changeNote").on("click", ".saveNote", function() {
                $("#loader-show").showLoading();
                var form = $("#noteForm");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function (result) {
                    if (result) {
                        $.notify(result.growl.options, result.growl.settings);
                    }
                    $("#loader-show").hideLoading();
                });
            });
            
        });'
);
$this->title = "Корзина";
?>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'checkout', 'timeout' => 5000]);
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
<section class="content">
    <div class="box box-info">
        <div class="box-header checkout-header">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-9">
                    <div class="btn-group" role="group" id="createAll">
                        <button class="btn btn-success" type="button"><i class="fa fa-paper-plane" style="margin-top:-3px;"></i><span class="hidden-xs"> Оформить все заказы</span></button>
                        <button type="button" class="btn btn-success  btn-outline total-cart">&nbsp;<span><?= $totalCart ?></span> <i class="fa fa-fw fa-rub"></i>&nbsp;</button>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-3">
                    <button class="btn btn-danger pull-right" type="button" id="deleteAll" style="margin-right: 10px;"><i class="fa fa-ban" style="margin-top:-3px;"></i><span class="hidden-xs"> Очистить корзину</span></button>    

                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="checkout">
                <?php foreach ($orders as $order) { ?>
                    <div class="box box-info box-order-content">
                        <div class="box-header with-border">
                            <div class="row">
                                <div class="col-md-8 col-sm-8 col-xs-8">
                                    <h3 class="box-title">Заказ у <?= $order->vendor->name ?> на сумму <span id="orderTotal<?= $order->id ?>" class="text-success"><?= $order->total_price ?></span><i class="fa fa-fw fa-rub text-success"></i></h3>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <div class="pull-right">
                                        <a class="btn btn-outline btn-xs btn-outline-danger delete" style="margin-right:10px;" data-id="<?= $order->id ?>"><i class="fa fa-close m-r-xxs" style="margin-top:-2px;"></i></a>
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
                                                <div class="col-md-6 col-sm-6 col-xs-6">
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
                                                        ]
                                                    ])
                                                    ?>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-xs-6">
                                                    <button class="btn btn-success create pull-right" data-id="<?= $order->id ?>"><i class="fa fa-paper-plane" style="margin-top:-3px;"></i><span class="hidden-fk"> Оформить заказ</span></button>
                                                    <a class="btn btn-gray comment pull-right"
                                                       data-target="#changeComment"
                                                       data-toggle="modal"
                                                       data-backdrop="static"
                                                       href="<?= Url::to(['order/ajax-set-comment', 'order_id' => $order->id]) ?>">
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
            </div>
        </div>
    </div>
    <?php Pjax::end() ?>
    <?=
    Modal::widget([
        'id' => 'changeComment',
        'clientOptions' => false,
    ])
    ?>
    <?=
    Modal::widget([
        'id' => 'changeNote',
        'clientOptions' => false,
    ])
    ?>
</section>
