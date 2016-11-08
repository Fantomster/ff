<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use common\models\Order;

$dataProvider->sort = false;
$discountTypes = Order::discountDropDown();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable order-table'],
    'options' => ['class' => 'table-responsive'],
   // 'panel' => false,
 //   'bootstrap' => false,
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
            'label' => 'Товар',
        ],
        [ 'format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return $data->price . ' <i class="fa fa-fw fa-rub"></i> / ' . $data->units;
            },
            'label' => 'Цена',
            'contentOptions' => ['class' => 'width150'],
        ],
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
            'label' => 'Количество',
            'contentOptions' => ['class' => 'width150'],
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) {
                return $data->total . ' <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Стоимость',
            'contentOptions' => ['class' => 'width150'],
        ],
    ],
]);
?>
    <table class="table dataTable tbl-discount">
        <tr>
            <th>
                <?= ($order->discount_type) ? $discountTypes[$order->discount_type] : 'Скидка' ?>
            </th><td>
                <?= ($order->discount) ? $order->discount : '-' ?>
            </td>
        </tr><tr>
            <th>
                Стоимость доставки
            </th><td>
                <?= $order->calculateDelivery() . ' <i class="fa fa-fw fa-rub"></i>' ?>
            </td>
        </tr><tr>
            <th>
                Стоимость заказа
            </th><td>
                <?= $order->total_price . ' <i class="fa fa-fw fa-rub"></i>' ?>
            </td>
        </tr>
    </table>