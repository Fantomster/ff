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
            'label' => Yii::t('message', 'frontend.views.order.grid_name', ['ru'=>'Наименование товара']),
            'value' => function ($data) {
                return htmlspecialchars_decode(htmlspecialchars_decode($data->product->product, true));
            },
            'headerOptions' => [
                'width' => '180'
            ],
        ],
        [
            'attribute' => 'comment',
            'header' => Yii::t('message', 'frontend.views.order.grid_comment', ['ru'=>'Комментарий']),
            'value' => function ($data) {
                return (isset($data->comment) ? $data->comment : '');
            },
            'headerOptions' => [
                'width' => '120'
            ],
        ],
        [
            'attribute' => 'article',
            'header' => Yii::t('message', 'frontend.views.order.grid_article', ['ru'=>'Артикул']),
            'contentOptions' => [
                'style' => 'text-align:center;',
            ],
            'headerOptions' => [
                'width' => '80'
            ],
        ],
        [
            'label' => Yii::t('message', 'frontend.views.order.grid_unit', ['ru'=>'Ед. измерения']),
            'attribute' => 'product.ed',
            'contentOptions' => [
                'style' => 'text-align:center;',
            ],
            'headerOptions' => [
                'width' => '103'
            ],
            'value' => function ($data) {
                return Yii::t('app', $data['product']['ed']);
            },
        ],
        [
            'attribute' => 'quantity',
            'value' => function ($data) {
                return number_format(round($data->quantity, 3), 3, '.', '');
            },
            'label' => Yii::t('message', 'frontend.views.order.grid_count_unit', ['ru'=>'Кол-во']),
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
            'label' => Yii::t('message', 'frontend.views.order.grid_price') . ' ' . $order->currency->iso_code,
            'contentOptions' => [
                'style' => 'text-align:right;',
            ],
            'headerOptions' => [
                'width' => '95'
            ],
        ],
        [
            'attribute' => 'total',
            'value' => function ($data) {
                return number_format(round($data->total, 2), 2, '.', '');
            },
            'label' => Yii::t('message', 'frontend.views.order.summ_three', ['ru'=>'Сумма']),
            'contentOptions' => [
                'style' => 'text-align:right;',
            ],
            'headerOptions' => [
                'width' => '90',
            ],
        ],
    ],
]);
?>