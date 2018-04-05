<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app','Заказы');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'id',
        'value' => function($data) {
            return Html::a($data['order_code'] ?? $data['id'], ['order/view', 'id' => $data['id']]);
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
//            'created_by_id',
//            'accepted_by_id',
    [
        'attribute' => 'status',
        'value' => 'statusText',
        'filter' => common\models\Order::getStatusList(),
    ],
    'total_price',
    [
        'attribute' => 'created_at',
        'label' => 'Дата заказа',
        'value' => function ($data) {
            return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
        }
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
//    'created_at',
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
        'batchSize' => 200,
        'timeout' => 0,
        'exportConfig' => [
            ExportMenu::FORMAT_PDF => false,
            ExportMenu::FORMAT_EXCEL => false,
            ExportMenu::FORMAT_EXCEL_X => [
                'label' => Yii::t('kvexport', 'Excel 2007+ (xlsx)'),
                'icon' => 'floppy-remove',
                'iconOptions' => ['class' => 'text-success'],
                'linkOptions' => [],
                'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                'alertMsg' => Yii::t('kvexport', 'The EXCEL 2007+ (xlsx) export file will be generated for download.'),
                'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'extension' => 'xlsx',
                'writer' => 'Excel2007'
            ],
        ],
    ]);
    ?>
    <?php Pjax::begin(['enablePushState' => true, 'id' => 'orderList', 'timeout' => 5000]); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => [ 'style' => 'table-layout:fixed;' ],
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>
