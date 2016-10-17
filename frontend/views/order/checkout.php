<?php

use yii\helpers\Url;
use yii\widgets\Pjax;

//use kartik\widgets\Growl;
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
        });'
);

$totalCart = 0;
foreach ($orders as $order) {
    $totalCart += $order->total_price;
}
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
                                <a class="btn btn-outline btn-xs btn-danger delete" style="margin-right:10px;" data-id="<?= $order->id ?>"><i class="fa fa-trash m-r-xxs" style="margin-top:-2px;"></i> Удалить все</a>
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
                                            <span style="font-size:16px; margin-top:5px; margin-right: 20px;" class="pull-right text-success">
                                                Всего: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> <i class="fa fa-fw fa-rub"></i>
                                            </span>
                                            <!--<label class="pull-right">Всего: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> руб</label>-->
                                        </div>
                                    </div>
                                    <div id="order<?= $order->id ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <?= $this->render('_checkoutContent', ['content' => $order->orderContent]) ?>
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