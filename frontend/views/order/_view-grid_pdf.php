<?php

use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;
$currencySymbol = $order->currency->iso_code;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered', 'style' => 'font-size: 11px!important;'],
    'columns' => [
        [
            'attribute' => 'product.product',
            'label' => 'Наименование товара',
        ],
        [
            'attribute' => 'article',
            'header' => 'Артикул'
        ],
        [
            'label' => 'Ед. измерения',
            'attribute' => 'product.ed'
        ],
        [
            'attribute' => 'quantity',
            'value' => function($data) {
                return number_format(round($data->quantity, 2), 2, ',', ' ');
            },
            'label' => 'Количество'
        ],
        [
            'attribute' => 'price',
            'value' => function($data) {
                return number_format(round($data->price, 2), 2, ',', ' ');
            },
            'label' => 'Цена за ед.,  ' . $currencySymbol,
        ],
        [
            'attribute' => 'total',
            'value' => function($data) {
                return number_format(round($data->total, 2), 2, ',', ' ');
            },
            'label' => 'Сумма',
        ],
    ],
]);
?>