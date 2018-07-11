<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

$columns = [
    [
        'format' => 'raw',
        'attribute' => 'order_id',
        'value' => function ($data) {
            return Html::a($data->order_id, ['order/edit', 'id' => $data->order_id]);
        },
        'label' => 'ID заказа',
        'group' => true,
    ],
    'file',
    [
        'format' => 'raw',
        'attribute' => 'created_at',
        'label' => 'Прикреплено',
        'value' => function ($data) {
            return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
        }
    ],
    [
        'format' => 'raw',
        'attribute' => 'assigned_to',
        'value' => function ($data) {
            return isset($data->assignment) ? $data->assignment->assigned_to : null;
        },
        'group' => true,
    ],
    [
        'format' => 'raw',
        'attribute' => 'is_processed',
        'value' => function ($data) {
            return isset($data->assignment) ? $data->assignment->is_processed : null;
        },
        'group' => true,
    ],
];
?>

<?php //Pjax::begin(['enablePushState' => true, 'id' => 'orderList', 'timeout' => 5000]); ?> 
<?=

kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['style' => 'table-layout:fixed;'],
    'columns' => $columns,
    'pjax' => true,
])
?>
<?php //Pjax::end(); ?>