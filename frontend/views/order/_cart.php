<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'cart', 'timeout' => 5000]);
foreach ($orders as $order) {
    ?>
    <div class="box box-info ">
        <div class="box-header with-border">
            <div class="col-md-10 col-xs-10">
                <h3 class="box-title"><?= $order->vendor->name ?></h3>
            </div><div class="col-md-2 col-xs-2">
                <span class='badge'><?= count($order->orderContent) ?></span>
            </div>
            <!--<h3 class="box-title"><?= $order->vendor->name . "<span class='badge'>" . count($order->orderContent) . "</span>" ?></h3>-->
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="list-group">
                <?php
                foreach ($order->orderContent as $position) {
                    $unit = empty($position->product->ed) ? "" : "/" . $position->product->ed;
                    ?>
                    <a class="list-group-item" 
                       data-vendor_id = "<?= $order->vendor_id ?>" 
                       data-product_id = "<?= $position->product_id ?>"
                       data-target="#changeQuantity"
                       data-toggle="modal"
                       data-backdrop="static"
                       href="<?= Url::to(['order/ajax-change-quantity', 'vendor_id' => $order->vendor_id, 'product_id' => $position->product_id]) ?>">
                        <?=
                            Html::button('<i class="fa fa-trash" style="margin-top:-2px;"></i>', [
                                    'class' => 'btn btn-outline-danger delete-position pull-right',
                                    'data-url' => Url::to(['/order/ajax-remove-position', 'vendor_id' => $order->vendor_id, 'product_id' => $position->product_id]),
                                ])
                        ?>
                        <h5 class="list-group-item-heading text-info"><?= $position->product_name ?> (<?= $position->price ?> <i class="fa fa-fw fa-rub"></i><?= $unit ?>)</h5>
                        <p class="list-group-item-text text-left">Кол-во: <?= $position->quantity + 0 ?></p>
                    </a>
    <?php } ?>
            </div>            

        </div>
        <div class="box-footer clearfix">
    <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;Оформить', ['order/checkout'], ['class' => 'btn btn-success pull-right', 'data-pjax' => 0]) ?>
        </div>
    </div>
    <?php
}
Pjax::end();
?>