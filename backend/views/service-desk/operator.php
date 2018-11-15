<?php

$this->title = Yii::t('app', 'Отчет по операторам');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="order-index">
    <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>
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
                'attribute'      => 'created_at',
                'filter'         => \kartik\date\DatePicker::widget([
                    'attribute' => 'created_at',
                    'model'     => $searchModel,
                    'language'  => 'ru',
                    'type'      => 1
                ]),
                'value'          => function ($data) {
                    return \Yii::$app->formatter->asDatetime($data['created_at'], 'php:d.m.Y');
                },
                'contentOptions' => [
                    'style' => 'width:120px'
                ],
                'label'          => 'Дата'
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
</div>