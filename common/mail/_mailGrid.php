<?php

use yii\grid\GridView;

$dataProvider->sort = false;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => [
        'style' => 'display: inline-block; margin-top: 15px; width: 100%; border-collapse: collapse;'
        ],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = ""; //$data->product->note ? "<div class='grid-article'>Заметка: " . $data->product->note . "</div>" : ""; 
                return "<div>" . $data->product_name . "</div>"
                        . "<div>". Yii::t('app', 'common.mail.mail_grid.art', ['ru'=>'Артикул:']) . $data->article . "</div>" . $note;
            },
            'label' => Yii::t('app', 'common.mail.mail_grid.good', ['ru'=>'Товар']),
            'contentOptions' => ['style' => 'border: 1px solid; width: 55%;'],
            'headerOptions' => ['style' => 'border: 1px solid; width: 55%;'],
        ],
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
            'label' => Yii::t('app', 'common.mail.mail_grid.amount', ['ru'=>'Количество']),
            'contentOptions' => ['style' => 'border: 1px solid; width: 15%;'],
            'headerOptions' => ['style' => 'border: 1px solid; width: 15%;'],
        ],
        [ 'format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) {
                return '<b>' . $data->price . ' ' . Yii::t('app', 'common.mail.mail_grid.rouble', ['ru'=>'руб']) . ' </b>';
            },
            'label' => Yii::t('app', 'common.mail.mail_grid.price', ['ru'=>'Цена']),
            'contentOptions' => ['style' => 'border: 1px solid; width: 15%;'],
            'headerOptions' => ['style' => 'border: 1px solid; width: 15%;'],
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) {
                return '<b>' . $data->total . ' ' . Yii::t('app', 'common.mail.mail_grid.rouble_two', ['ru'=>'руб']) . ' </b>';
            },
            'label' => Yii::t('app', 'common.mail.mail_grid.sum', ['ru'=>'Сумма']),
            'contentOptions' => ['style' => 'border: 1px solid; width: 15%;'],
            'headerOptions' => ['style' => 'border: 1px solid; width: 15%;'],
        ],
    ],
]);
?>