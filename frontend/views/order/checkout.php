<?php

use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;

kartik\growl\GrowlAsset::register($this);

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
            $("body").on("hidden.bs.modal", "#changeComment", function() {
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
            
        });'
);
?>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'checkout', 'timeout' => 3000]);
?>

<div class="box box-info">
    <div class="box-header checkout-header">
        <h3 class="box-title">
            <i class="fa fa-shopping-cart m-r-sm" style="margin-top:-3px;"></i> Корзина
        </h3>
        <div class="btn-group pull-right" role="group" id="createAll">
            <button class="btn btn-success m-t-xs" type="button"><i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i> Оформить все заказы</button>
            <button type="button" class="btn btn-success  btn-outline m-t-xs total-cart">&nbsp;<span><?= $totalCart ?></span> <i class="fa fa-fw fa-rub"></i>&nbsp;</button>
        </div>
        <button class="btn btn-danger btn-outline  m-t-xs m-r pull-right" type="button" id="deleteAll" style="margin-right: 10px;"><i class="fa fa-trash" style="margin-top:-3px;"></i> Очистить корзину</button>    
    </div>
    <div class="box-body">
        <div class="checkout">
            <?php foreach ($orders as $order) { ?>
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Заказ у <?= $order->vendor->name ?></h3>
                        <div class="pull-right">
                            <a class="btn btn-outline btn-xs btn-danger delete" style="margin-right:10px;" data-id="<?= $order->id ?>"><i class="fa fa-trash m-r-xxs" style="margin-top:-2px;"></i> Удалить заказ</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="panel-group">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="form-inline">
                                        <button class="btn btn-default" data-toggle="collapse" data-target="#order<?= $order->id ?>">Содержимое заказа</button>
                                        <button class="btn btn-success pull-right create" data-id="<?= $order->id ?>"><i class="fa fa-paper-plane" style="margin-top:-3px;"></i> Оформить заказ</button>
                                        <a class="btn btn-default pull-right comment margin-right-15"
                                           data-target="#changeComment"
                                           data-toggle="modal"
                                           data-backdrop="static"
                                           href="<?= Url::to(['order/ajax-set-comment', 'order_id' => $order->id]) ?>">
                                            <i class="fa fa-comment" style="margin-top:-3px;"></i> Комментарий к заказу
                                        </a>
                                        <div class="pull-right padding-right-15">
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
                                                'pluginOptions' => [
                                                    'daysOfWeekDisabled' => $order->vendor->getDisabledDeliveryDays(),
                                                    'format' => 'dd.mm.yyyy',
                                                    'autoclose' => true,
                                                    'startDate' => "0d",
                                                ]
                                            ])
                                            ?>
                                        </div>
                                        <span style="font-size:16px; margin-top:5px; margin-right: 20px;" class="pull-right text-success">
                                            Всего: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> <i class="fa fa-fw fa-rub"></i>
                                        </span>
                                        <!--<label class="pull-right">Всего: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> руб</label>-->
                                    </div>
                                </div>
                                <div id="order<?= $order->id ?>" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <?= $this->render('_checkoutContent', ['content' => $order->orderContent, 'vendor_id' => $order->vendor_id]) ?>
                                    </div>
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