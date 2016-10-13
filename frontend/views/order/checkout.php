<?php
use yii\helpers\Url;

$this->registerJs(
        '$("document").ready(function(){
            //
        });'
);
?>

<div class="row checkout">
    <div class="col-md-9">
        <?php foreach ($orders as $order) { ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Заказ у <?= $order->vendor->name ?></h3>
                    <div class="pull-right">
                        <a class="btn btn-outline btn-xs btn-danger" style="margin-right:10px;"><i class="fa fa-trash m-r-xxs" style="margin-top:-2px;"></i> Удалить все</a>
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
                                        Всего: <span id="orderTotal<?= $order->id ?>"><?= $order->total_price ?></span> руб
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