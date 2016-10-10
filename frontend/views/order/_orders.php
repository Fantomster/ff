<?php

use yii\helpers\Html;
?>
<!--<div class="list-group">-->
<?php foreach ($orders as $order) { ?>
    <div class="box box-info ">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $order['vendor_name'] . "&nbsp;<span class='badge'>" . count($order['content']) . "</span>" ?></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="list-group">
            <?php foreach ($order['content'] as $product) { ?>
                <a class="list-group-item">
                    <!--<button class="btn btn-danger btn-outline pull-right"><i class="fa fa-trash" style="margin-top:-2px;"></i></button>-->
                    <?= 
                        Html::button(
                               '<i class="fa fa-trash" style="margin-top:-2px;"></i>',
                                [
                                    'class' => "btn btn-danger btn-outline pull-right",
                                    'data' => [
                                        'vendor_id' => $order['vendor_id'],
                                        'product_id' => $product['product_id'],
                                    ]
                                ]
                        )
                    ?>
                    <h5 class="list-group-item-heading text-info"><?= $product['product_name'] ?> (<?= $product['price'] ?> руб/<?= $product['units'] ?>)</h5>
                    <p class="list-group-item-text text-left">Кол-во: <?= $product['quantity'] ?></p>
                </a>
            <?php } ?>
            </div>            

        </div>
        <div class="box-footer clearfix">
            <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;Оформить', ['order/checkout'], ['class' => 'btn btn-success pull-right']) ?>
            <!--<a href="#" class="btn btn-success pull-right">Оформить</a>-->
        </div>
    </div>
    <?php
//        echo Html::a("Заказ у $order[vendor_name] &nbsp;<span class='badge'>" . count($order['content']) . "</span>", ['order/ajax-show-order', 'vendor_id' => $order['vendor_id']], [
//            '',
//            'class' => 'list-group-item show-order',
//            'data-id' => $order['vendor_id'],
//            'data' => [
//                'target' => '#showOrder',
//                'toggle' => 'modal',
//                'backdrop' => 'static',
//            ]
//        ]);
}
?>
<!--</div>-->