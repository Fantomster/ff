<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

Pjax::begin(['enablePushState' => false, 'id' => 'productsList', 'timeout' => 3000]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'summary' => '',
    'columns' => [
        [
            'attribute' => 'baseProduct.product',
            'value' => 'baseProduct.product',
            'label' => 'Название продукта',
        ],
        [
            'attribute' => 'organization.name',
            'value' => 'organization.name',
            'label' => 'Поставщик',
        ],
        'price',
        [
            'attribute' => 'baseProduct.units',
            'value' => 'baseProduct.units',
            'label' => 'Количество в упаковке',
        ],
        'note',
        [
            'format' => 'raw',
            'value' => function ($data) {
                $link = Html::a('<span class="glyphicon glyphicon-plus"></span>', '#', [
                            'class' => 'add-to-cart',
                            'data-id' => $data->id,
                ]);
                return $link;
            },
                ],
            ],
        ]);
Pjax::end();        