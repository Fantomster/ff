<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;
$discountTypes = $order->discountDropDown();
$currencySymbol = $order->currency->symbol;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterPosition' => false,
    'summary' => '',
    'headerRowOptions' => ['style' => "padding: 3px;"],
    'tableOptions' => ['class' => 'table'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = isset($data->comment) ? '<p style="line-height: 1;font-size: 11px;color: #999C9E;"> ' . Yii::t('app', 'frontend.views.order.view_grid.note', ['ru'=>'Заметка']) . ':'. $data->comment . '</p>' : "";
                return '<p style="font-size: 16px;color: #2C9EE5; font-family: Circe_Bold">' . Html::decode(Html::decode($data->product_name)) . '</p>
                    <p style="line-height: 1;font-size: 11px;color: #999C9E;">' . Yii::t('message', 'frontend.views.order.art_two', ['ru'=>'Артикул:']) . '  ' . $data->article . '</p>'.$note;
            },
            'label' => Yii::t('app', 'frontend.views.order.view_grid.good.', ['ru'=>'Товар']),
            'headerOptions' => ['style' => "padding: 10px;width: 40%;border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        [
            'attribute' => 'quantity',
            'value' => 'quantity',
            'label' => Yii::t('message', 'frontend.views.order.amount_two', ['ru'=>'Количество']),
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        ['format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) use ($currencySymbol) {
                return '<b>' . $data->price . '</b> ' . $currencySymbol;
            },
            'label' => Yii::t('message', 'frontend.views.order.price_two', ['ru'=>'Цена']),
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) use ($currencySymbol) {
                $sum = $data->price * $data->quantity;
                return '<b>' . number_format($sum, 2, '.', '') . '</b> '.$currencySymbol.'</i>';
            },
            'label' => Yii::t('message', 'frontend.views.order.summ_three', ['ru'=>'Сумма']),
            'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
            'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
        ],
    ],
]);
?>