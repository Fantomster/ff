<?php

$this->title = 'Доступы 1C "Общепит"';

$this->params['breadcrumbs'][] = [
    'label' => 'Управление лицензиями',
    'url'   => '/integration'
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="catalog-index">
    <div>
        <h2><?= $this->title ?></h2>
        <a class="btn btn-success pull-right" href="/odinsobsh/create">Создать</a>
    </div>

    <div class="box-header with-border">
        <div class="box-title pull-left">
            <?=
            \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel'  => $searchModel,
                'summary'      => false,
                'columns'      => [
                    'fd',
                    'td',
                    [
                        'attribute' => 'org',
                        'label'     => 'Организация MixCart',
                        'value'     => function ($model) {
                            if (isset($model))
                                return $model->organization ? $model->organization->name : null;

                        },
                    ],
                    [
                        'attribute' => 'status_id',
                        'filter'    => [0 => 'Не активно', 1 => 'Активно'],
                        'value'     => function ($model) {
                            if ($model) return ($model->status_id == 1) ? 'Активно' : 'Не активно';
                        },
                    ],
                    [
                        'class'    => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
