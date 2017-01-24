<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

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
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
            'label' => 'Количество',
            'contentOptions' => ['class' => 'width150'],
        ],
        [ 'format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return '<b>' . $data->price . '</b> <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Цена',
            'contentOptions' => ['class' => 'width150'],
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) {
                return '<b>' . $data->total . '</b> <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Сумма',
            'contentOptions' => ['class' => 'width150'],
        ],
    ],
]);
?>
                    <div class="order-total">
                        <div class="row">
                            <div class="col-xs-4"><hr></div>
                            <div class="col-xs-8"></div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4">
                                <span><?= ($order->discount_type) ? $discountTypes[$order->discount_type] : 'Скидка' ?></span>
                            </div>
                            <div class="col-xs-8">
                                <span><?= ($order->discount) ? $order->discount : '-' ?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 ">
                                <span>Стоимость доставки:</span>
                            </div>
                            <div class="col-xs-8">
                                <span><?= $order->calculateDelivery() . ' руб' ?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4">
                                <span>Итого:</span>
                            </div>
                            <div class="col-xs-8">
                                <span><?= $order->total_price . ' руб' ?></span>
                            </div>
                        </div>
                    </div>

<?=
(isset($canRepeatOrder) && $canRepeatOrder) ? Html::a('<i class="icon fa fa-refresh"></i> Повторить заказ', ['order/repeat', 'id' => $order->id], [
            'class' => 'btn btn-default pull-right',
            'style' => 'margin-right: 7px;'
        ]) : "" ?>