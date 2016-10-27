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
