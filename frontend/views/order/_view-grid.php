<?php

use kartik\grid\GridView;

$dataProvider->sort = false;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'panel' => false,
    'bootstrap' => false,
    'columns' => [
        'product.product',
        'quantity',
        [ 'format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return $data->price . ' <i class="fa fa-fw fa-rub"></i>';
            },
            'label' => 'Цена',
        ],
    ],
]);
