<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'cart', 'timeout' => 3000]);
foreach ($orders as $order) {
    ?>
    <div class="box box-info ">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $order->vendor->name . "&nbsp;<span class='badge'>" . count($order->orderContent) . "</span>" ?></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="list-group">
    <?php foreach ($order->orderContent as $position) { ?>
                    <a class="list-group-item" 
                       data-vendor_id = "<?= $order->vendor_id ?>" 
                       data-product_id = "<?= $position->product_id ?>"
                       data-target="#changeQuantity"
                       data-toggle="modal"
                       data-backdrop="static"
                       href="<?= Url::to(['order/ajax-change-quantity', 'vendor_id' => $order->vendor_id, 'product_id' => $position->product_id]) ?>">
                        <button class="btn btn-danger btn-outline pull-right"><i class="fa fa-trash" style="margin-top:-2px;"></i></button>
                        <h5 class="list-group-item-heading text-info"><?= $position->product_name ?> (<?= $position->price ?> <i class="fa fa-fw fa-rub"></i>/<?= $position->units ?>)</h5>
                        <p class="list-group-item-text text-left">Кол-во: <?= $position->quantity ?></p>
                    </a>
    <?php } ?>
            </div>            

        </div>
        <div class="box-footer clearfix">
    <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;Оформить', ['order/checkout'], ['class' => 'btn btn-success pull-right']) ?>
        </div>
    </div>
    <?php
}
Pjax::end();
?>