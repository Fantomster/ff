<?php

use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'pdf-table'],
    'columns' => [
        [
            'attribute' => 'product.product',
            'label' => Yii::t('app', 'Наименование товара'),
            'value' => function ($data) {
                return htmlspecialchars_decode(htmlspecialchars_decode($data->product->product, true));
            },
            'headerOptions' => [
                'width' => '180'
            ],
        ],
        [
            'attribute' => 'note.note',
            'header' => Yii::t('app', 'Комментарий'),
            'value' => function ($data) {
                return (isset($data->note->note) ? $data->note->note : '');
            },
            'headerOptions' => [
                'width' => '120'
            ],
        ],
        [
            'attribute' => 'article',
            'header' => Yii::t('app', 'Артикул'),
            'contentOptions' => [
                'style' => 'text-align:center;',
            ],
            'headerOptions' => [
                'width' => '80'
            ],
        ],
        [
            'label' => Yii::t('app', 'Ед. измерения'),
            'attribute' => 'product.ed',
            'contentOptions' => [
                'style' => 'text-align:center;',
            ],
            'headerOptions' => [
                'width' => '103'
            ]
        ],
        [
            'attribute' => 'quantity',
            'value' => function ($data) {
                return number_format(round($data->quantity, 3), 3, '.', '');
            },
            'label' => Yii::t('app', 'Кол-во'),
            'contentOptions' => [
                'style' => 'text-align:right;',
            ],
            'headerOptions' => [
                'width' => '50'
            ],
        ],
        [
            'attribute' => 'price',
            'value' => function ($data) use ($order) {
                return number_format(round($data->price, 2), 2, '.', '');
            },
            'label' => Yii::t('app', 'Цена за ед.,  ') . $order->currency->iso_code,
            'contentOptions' => [
                'style' => 'text-align:right;',
            ],
            'headerOptions' => [
                'width' => '115'
            ],
        ],
        [
            'attribute' => 'total',
            'value' => function ($data) {
                return number_format(round($data->total, 2), 2, '.', '');
            },
            'label' => Yii::t('app', 'Сумма'),
            'contentOptions' => [
                'style' => 'text-align:right;',
            ],
            'headerOptions' => [
                'width' => '70',
            ],
        ],
    ],
]);
?>