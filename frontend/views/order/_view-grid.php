<?php

use kartik\grid\GridView;
use common\models\Order;

$dataProvider->sort = false;
$discountTypes = Order::discountDropDown();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'panel' => false,
    'bootstrap' => false,
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = ""; //$data->product->note ? "<div class='grid-article'>Заметка: " . $data->product->note . "</div>" : ""; 
                return "<div class='grid-prod'>" . $data->product_name . "</div>"
                        . "<div class='grid-article'>Артикул: <span>"
                        . $data->article . "</span></div>" . $note;
            },
            'label' => 'Название продукта',
        ],
        [ 'format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return $data->price . ' <i class="fa fa-fw fa-rub"></i> / ' . $data->units;
            },
            'label' => 'Цена',
        ],
        'quantity',
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) {
                return $data->total . ' <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Общая стоимость',
        ],
    ],
]);
?>
<div class="pull-right">
<table class="table table-bordered table-striped dataTable" style="width: 400px">
    <tr>
        <td>
            <?= ($order->discount_type) ? $discountTypes[$order->discount_type] : 'Скидка' ?>
        </td><td>
            <?= ($order->discount) ? $order->discount : '-' ?>
        </td>
    </tr><tr>
        <td>
            Стоимость доставки
        </td><td>
            <?= $order->calculateDelivery() . ' <i class="fa fa-fw fa-rub"></i>' ?>
        </td>
    </tr><tr>
        <td>
            Стоимость заказа
        </td><td>
            <?= $order->total_price . ' <i class="fa fa-fw fa-rub"></i>' ?>
        </td>
    </tr>
</table>
</div>