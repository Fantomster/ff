<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'id',
        'value' => function($data) {
            return Html::a($data['id'], ['order/view', 'id' => $data['id']]);
        },
    ],
    [
        'format' => 'raw',
        'attribute' => 'client_name',
//        'value' => 'client.name',
        'value' => function ($data) {
            return Html::a($data['client']['name'], ['organization/view', 'id' => $data['client_id']]);
        },
        'label' => 'Ресторан',
    ],
    [
        'format' => 'raw',
        'attribute' => 'vendor_name',
//        'value' => 'vendor.name',
        'value' => function ($data) {
            return Html::a($data['vendor']['name'], ['organization/view', 'id' => $data['vendor_id']]);
        },
        'label' => 'Поставщик',
    ],
    [
        'format' => 'raw',
        'attribute' => 'client_manager',
//        'value' => 'createdByProfile.full_name',
        'value' => function ($data) {
            return Html::a($data['createdByProfile']['full_name'], ['client/view', 'id' => $data['created_by_id']]);
        },
        'label' => 'Создан',
    ],
    [
        'format' => 'raw',
        'attribute' => 'vendor_manager',
//        'value' => 'acceptedByProfile.full_name',
                'value' => function ($data) {
            return Html::a($data['acceptedByProfile']['full_name'], ['client/view', 'id' => $data['accepted_by_id']]);
        },
        'label' => 'Принят',
    ],
//            'created_by_id',
//            'accepted_by_id',
    [
        'attribute' => 'status',
        'value' => 'statusText',
        'filter' => common\models\Order::getStatusList(),
    ],
    'total_price',
    'created_at',
        // 'updated_at',
//             'requested_delivery',
//             'actual_delivery',
        // 'comment:ntext',
        // 'discount',
        // 'discount_type',
];
?>
<div class="order-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'target' => ExportMenu::TARGET_SELF,
        'exportConfig' => [
            ExportMenu::FORMAT_PDF => false,
            ExportMenu::FORMAT_EXCEL_X => false,
        ],
    ]);
    ?>
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'orderList', 'timeout' => 5000]); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
