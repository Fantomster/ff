<?php

use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;
$discountTypes = Order::discountDropDown();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-striped'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function ($data) {
                return
                    Html::tag('a', Html::decode(Html::decode($data->product_name)), ['href' => '/goods/'.$data->product_id]).
                    Html::tag('p', \Yii::t('app', 'Артикул').': '.$data->article, [
                        'style' => 'line-height: 1;font-size: 11px;color: #999C9E;'
                    ]).
                    Html::tag('p', $data->note->note, [
                        'style' => 'line-height: 1;font-size: 11px;color: #999C9E;'
                    ]);
            },
        ],
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
        ],
        ['format' => 'raw',
            'attribute' => 'price',
            'value' => function ($data) {
                return $data->price . ' ' .$data->currency->symbol;
            },
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function ($data) {
                return $data->total . ' ' .$data->currency->symbol;
            },
        ],
    ],
]);
?>