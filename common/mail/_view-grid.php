<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use common\models\Order;
use yii\helpers\Html;

$dataProvider->sort = false;
$discountTypes = $order->discountDropDown();
$currencySymbol = $order->currency->symbol;

if (!Yii::$app instanceof Yii\console\Application){
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterPosition' => false,
        'summary' => '',
        'headerRowOptions' => ['style' => "padding: 3px;"],
        'tableOptions' => ['class' => 'table', 'style' => 'width:100%;'],
        'columns' => [
            [
                'format' => 'raw',
                'attribute' => 'product.product',
                'value' => function($data) {
                    $note = isset($data->comment) ? '<p style="mso-line-height-rule: exactly;line-height:11px;font-size: 11px;color: #999C9E;">' . Yii::t('app', 'common.mail.view_grid.article', ['ru'=>'Заметка: ']) . $data->comment . '</p>' : "";
                    return '<p style="font-size: 16px;color: #2C9EE5; font-family: Circe_Bold">' . Html::decode(Html::decode($data->product_name)) . '</p>
                    <p style="mso-line-height-rule: exactly;line-height:11px;font-size: 11px;color: #999C9E;">' . Yii::t('app', 'common.mail.view_grid.art', ['ru'=>'Артикул:']) . '  ' . $data->article . '</p>'.$note;
                },
                'label' => Yii::t('app', 'common.mail.view_grid.good', ['ru'=>'Товар']),
                'headerOptions' => ['style' => "padding: 10px;width: 40%;border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
                'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
            ],
            [
                'attribute' => 'quantity',
                'value' => 'quantity',
                'label' => Yii::t('app', 'common.mail.view_grid.amount', ['ru'=>'Количество']),
                'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
                'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
            ],
            ['format' => 'raw',
                'attribute' => 'price',
                'value' => function($data) use ($currencySymbol) {
                    return '<b>' . $data->price . ' ' . $currencySymbol . '</b>';
                },
                'label' => Yii::t('app', 'common.mail.view_grid.price', ['ru'=>'Цена']),
                'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
                'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
            ],
            [
                'format' => 'raw',
                'attribute' => 'total',
                'value' => function($data) use ($currencySymbol) {
                    return '<b>' . $data->total . ' ' . $currencySymbol . '</b>';
                },
                'label' => Yii::t('app', 'common.mail.view_grid.sum', ['ru'=>'Сумма']),
                'headerOptions' => ['style' => "border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;"],
                'contentOptions' => ['style' => 'border-top: 1px solid #ddd;'],
            ],
        ],
    ]);
}
?>