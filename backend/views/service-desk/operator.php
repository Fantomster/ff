<?php

use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Отчет по операторам');
$this->params['breadcrumbs'][] = $this->title;

$js = <<< JS
$("document").ready(function () {
    var justSubmitted = false;
    $(".order-index").on("change", "#filter-date, #filter-date-2", function () {
        if (!justSubmitted) {
            $("#search-form").submit();
            justSubmitted = true;
            setTimeout(function () {
                justSubmitted = false;
            }, 500);
        }
    });
});   
JS;
$this->registerJs($js);

?>
<div class="order-index">
    <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);
    $form = ActiveForm::begin([
        'options'                => [
            'data-pjax' => true,
            'id'        => 'search-form',
            'role'      => 'search',
        ],
        'enableClientValidation' => false,
        'method'                 => 'get',
    ]);
    ?>
    <div class="form-group">
        <?=
        DatePicker::widget([
            'name'          => 'date_from',
            'id'            => 'filter-date',
            'value'         => $filterValues['date_from'],
            'type'          => DatePicker::TYPE_RANGE,
            'name2'         => 'date_to',
            'value2'        => $filterValues['date_to'],
            'separator'     => '-',
            'pluginOptions' => [
                'autoclose'      => true,
                'format'         => 'dd-mm-yyyy',
                'todayHighlight' => true,
                'endDate'        => "0d",
            ],
            'removeButton'  => false,
        ]);
        ?>
    </div>

    <div>
        <?php
        echo \kartik\grid\GridView::widget([
            'dataProvider' => $totalOperatorsDataProvider,
            'filterModel'  => $searchModel,
            'options'      => ['style' => 'table-layout:fixed;'],
            'columns'      => [
                [
                    'attribute' => 'operator_name',
                    'label'     => 'Оператор'
                ],
                [
                    'attribute' => 'cnt_order',
                    'label'     => 'Кол-во заказов в статусе заказа'
                ],
                [
                    'attribute' => 'cnt_order_changed',
                    'label'     => 'Кол-во заказов отредактированных поставщиком'
                ],
            ]
        ]);
        ?>
    </div>

    <?php
    echo \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'options'      => ['style' => 'table-layout:fixed;'],
        'columns'      => [
            [
                'attribute' => 'cnt_order',
                'label'     => 'Кол-во заказов в статусе заказа'
            ],
            [
                'attribute' => 'cnt_order_changed',
                'label'     => 'Кол-во заказов отредактированных поставщиком'
            ],
            [
                'attribute' => 'operator_name',
                'label'     => 'Оператор'
            ],
            [
                'attribute' => 'created_at',
                'label'     => 'Дата',
                'value'     => function ($data) {
                    return \Yii::$app->formatter->asDatetime($data['dt'], 'php:d-m-Y');
                },
                'filter'    => false
            ],
            [
                'attribute' => 'cnt_vendor',
                'label'     => 'Кол-во поставщиков'
            ],
            [
                'attribute' => 'status',
                'label'     => 'Статус заказа',
                'filter'    => false
            ],
            [
                'attribute' => 'status_call_id',
                'label'     => 'Статус обработки оператором'
            ],
            [
                'attribute' => 'avg_resolve_mins',
                'label'     => 'Среднее время от взятия до завершения оператором'
            ],

        ]
    ]);
    ?>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end() ?>
</div>