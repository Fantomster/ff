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
    'headerRowOptions' => ['style' => "padding: 3px;"],
    'tableOptions' => ['class' => 'table'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = isset($data->note->note) ? '<p style="line-height: 0.5;font-size: 11px;color: #999C9E;">Заметка: ' . $data->note->note . '</p>' : ""; 
                return '<p style="font-size: 16px;color: #2C9EE5; font-family: Circe_Bold">' . Html::decode(Html::decode($data->product_name)) . '</p>
                    <p style="line-height: 1;font-size: 11px;color: #999C9E;">Артикул: ' . $data->article . '</p>'.$note;
            },
            'label' => 'Товар',
            'headerOptions' => ['style' => "padding: 10px;width: 40%;border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
            'label' => 'Количество',
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        ['format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return '<b>' . $data->price . '</b> <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Цена',
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) {
                return '<b>' . $data->total . '</b> <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Сумма',
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
    ],
]);
?>