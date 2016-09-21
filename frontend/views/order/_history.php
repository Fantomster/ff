<?php
use yii\grid\GridView;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'columns' => [
        'client_id',
        'vendor_id',
        'created_by_id',
        'accepted_by_id',
        'status',
        'total_price',
        'created_at',
        'updated_at',
    ],
    'rowOptions'   => function ($model, $key, $index, $grid) {
        return ['data-id' => $model->id];
    },
]);