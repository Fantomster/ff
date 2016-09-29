<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

Pjax::begin(['enablePushState' => false, 'id' => 'productsList', 'timeout' => 3000]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'filterPosition' => false,
    'summary' => '',
    'columns' => [
        [
            'attribute' => 'product',
            'value' => function($data){
                   return $data['product'];
               },
            'label' => 'Название продукта',
        ],
        [
            'attribute' => 'price',
            'value' => function($data) {
                   return $data['price']  . ' / ' . $data['units'];
            },
            'label' => 'Цена'
        ],
        [
            'format' => 'raw',
            'value' => function($data){
                return Html::textInput('', 1);
            },
            'label' => 'Количество'
        ],
        //'note',
        [
            'format' => 'raw',
            'value' => function ($data) {
                $link = Html::a('<span class="glyphicon glyphicon-plus"></span>', '#', [
                            'class' => 'add-to-cart',
                            'data-id' => $data['id'],
                ]);
                return $link;
            },
        ],
    ],
]);
Pjax::end();        