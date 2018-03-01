<?php
use \common\models\Order;

$columns = [
    [
        'attribute' => 'id',
        'value' => 'id',
        'label' => '№',
    ],
    [
        'attribute' => 'vendor.name',
        'value' => 'vendor.name',
        'label' => Yii::t('message', 'frontend.views.client.index.vendor', ['ru'=>'Поставщик']),
    ],
    [
        'attribute' => 'createdByProfile.full_name',
        'value' => 'createdByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.client.index.created', ['ru'=>'Заказ создал']),
    ],
    [
        'attribute' => 'acceptedByProfile.full_name',
        'value' => 'acceptedByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.client.index.rec', ['ru'=>'Заказ принял']),
    ],
    [
        'format' => 'raw',
        'attribute' => 'total_price',
        'value' => function($data) {
            return "<b>$data->total_price</b> " . $data->currency->symbol;
        },
        'label' => Yii::t('message', 'frontend.views.client.index.sum', ['ru'=>'Сумма']),
    ],
    [
        'format' => 'raw',
        'attribute' => 'created_at',
        'value' => function($data) {
            $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
        },
        'label' => Yii::t('message', 'frontend.views.client.index.created_at', ['ru'=>'Дата создания']),
    ],
    [
        'format' => 'raw',
        'attribute' => 'status',
        'value' => function($data) {
            switch ($data->status) {
                case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                    $statusClass = 'new';
                    break;
                case Order::STATUS_PROCESSING:
                    $statusClass = 'processing';
                    break;
                case Order::STATUS_DONE:
                    $statusClass = 'done';
                    break;
                case Order::STATUS_REJECTED:
                case Order::STATUS_CANCELLED:
                    $statusClass = 'cancelled';
                    break;
            }
            return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>'; //<i class="fa fa-circle-thin"></i>
        },
        'label' => Yii::t('message', 'frontend.views.client.index.status', ['ru'=>'Статус']),
    ],
    [
        'format' => 'raw',
        'value' => function($data) use ($invoice_id) {
            return \yii\helpers\Html::a('Привязать к заказу', '#', [
                'class' => 'reorder btn btn-outline-processing btn-xs create-order',
                'data' => [
                    'order_id' => $data->id,
                    'invoice_id' => $invoice_id,
                    'vendor_id' => $data->vendor->id
                ],
            ]);
        },
        'contentOptions' => ['class' => 'text-center'],
        'headerOptions' => ['style' => 'width: 20px;']
    ],
];
?>

<?php

\yii\widgets\Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);


echo \kartik\grid\GridView::widget([
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'dataProvider' => $dataProvider,
    'summary' => false,
    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'columns' => $columns
]);

\yii\widgets\Pjax::end();