<?php

use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-striped table-bordered', 'style' => 'font-size: 11px;width:100%;'],
    'showOnEmpty' => false,
    'columns' => [
        [
            'attribute' => 'product.product',
            'label' => 'Наименование товара',
            'headerOptions' => [
                'width' => '180'
            ],
        ],
        [
            'attribute' => 'note.note',
            'header' => 'Комментарий',
            'value' => function($data){
                return (isset($data->note->note) ? $data->note->note : '');
            },
            'headerOptions' => [
                'width' => '120'
            ],
        ],
        [
            'attribute' => 'article',
            'header' => 'Артикул',
            'contentOptions' => [
                'style' => 'text-align:center;vertical-align:middle',
            ],
            'headerOptions' => [
                'width' => '80'
            ],
        ],
        [
            'label' => 'Ед. измерения',
            'attribute' => 'product.ed',
            'headerOptions' => [
                'width' => '103',
            ],
            'contentOptions' => [
                'style' => 'text-align:center;vertical-align:middle',
            ]
        ],
        [
            'attribute' => 'quantity',
            'value' => function($data) {
                return number_format(round($data->quantity, 2), 2, '.', '');
            },
            'label' => 'Кол-во',
            'contentOptions' => [
                'style' => 'text-align:right;vertical-align:middle',
            ],
            'headerOptions' => [
                'width' => '50'
            ],
        ],
        [
            'attribute' => 'price',
            'value' => function($data) use ($order) {
                return number_format(round($data->price, 2), 2, '.', '');
            },
            'label' => 'Цена за ед.,  ' . $order->currency->iso_code,
            'contentOptions' => [
                'style' => 'text-align:right;vertical-align:middle',
            ],
            'headerOptions' => [
                'width' => '115'
            ],
        ],
        [
            'attribute' => 'total',
            'value' => function($data) {
                return number_format(round($data->total, 2), 2, '.', '');
            },
            'label' => 'Сумма',
            'contentOptions' => [
                'style' => 'text-align:right;vertical-align:middle',
            ],
            'headerOptions' => [
                'width' => '70'
            ],
        ],
    ],
]);
?>