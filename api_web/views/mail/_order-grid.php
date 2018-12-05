<?php

//use kartik\grid\GridView;
use yii\grid\GridView;

$dataProvider->sort = false;
$discountTypes = $order->discountDropDown();
$currencySymbol = $order->currency->symbol;

if (!Yii::$app instanceof Yii\console\Application) {
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterPosition' => false,
        'summary' => '',
        'tableOptions' => ['width' => '100%', 'style' => "table-layout: fixed;border-collapse:collapse;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;"],
        'rowOptions' => ['style' => "border-bottom: 2px solid #F0F4F2;"],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => "width: 7%;background: #f3f3f3;font-weight: normal;"],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 0 12px 20px;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('app', 'common.mail.view_grid.good', ['ru' => 'Товар']) . "</span>",
                'value' => function($data) {
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$data->product_name}</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 24%;padding: 14px 5px 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 8px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('message', 'frontend.views.order.grid_article', ['ru' => 'Артикул']) . "</span>",
                'value' => function($data) {
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$data->article}</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 11%;padding: 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 5px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('message', 'frontend.views.order.grid_count_unit', ['ru' => 'Количество']) . "</span>",
                'value' => function($data) {
                    $quantity = number_format($data->quantity, 2, '.', '');
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$quantity}</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 9%;padding: 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 5px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px; color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('app', 'common.mail.view_grid.price', ['ru' => 'Цена']) . "</span>",
                'value' => function($data) use ($currencySymbol) {
                    $price = number_format($data->price, 2, '.', '');
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$price} $currencySymbol/{$data->product->ed}</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 14%;padding: 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 5px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px; color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('app', 'common.mail.view_grid.sum', ['ru' => 'Сумма']) . "</span>",
                'value' => function($data) use ($currencySymbol) {
                    $total = number_format($data->total, 2, '.', '');
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$total} $currencySymbol</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 12%;padding: 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "font-weight: normal;padding: 12px 5px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px; color: #8c8f8d;"
                ],
            ],
            [
                'format' => 'raw',
                'header' => "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;\">" . Yii::t('message', 'frontend.views.order.grid_comment', ['ru' => 'Комментарий']) . "</span>",
                'value' => function($data) {
                    return "<span style=\"font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;color: #8c8f8d;\">{$data->comment}</span>";
                },
                'headerOptions' => [
                    'style' => "text-align: left;font-weight: normal;width: 23%;padding: 14px 0;font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #000000;background: #f3f3f3;"
                ],
                'contentOptions' => [
                    'valign' => 'top',
                    'style' => "padding: 12px 20px 12px 0;background: #ffffff !important;font-family: 'Open Sans', Arial, sans-serif;font-size: 12px;font-style: italic; color: #8c8f8d;"
                ],
            ],
        ],
    ]);
} 