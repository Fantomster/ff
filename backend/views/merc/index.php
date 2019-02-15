<?php

use kartik\grid\GridView;

$this->title = 'Доступы ВЕТИС "Меркурий"';

$this->params['breadcrumbs'][] = [
    'label' => 'Управление лицензиями',
    'url'   => '/integration'
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="catalog-index">
    <div>
        <h2><?= $this->title ?></h2>
        <a class="btn btn-success pull-right" href="/merc/create">Создать</a>
    </div>

    <div class="box-header with-border">
        <div class="box-title pull-left">
            <?=
            \kartik\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel'  => $searchModel,
                'summary'      => false,
                'columns'      => [
                    [
                        'attribute'           => 'fd',
                        'filterType'          => \kartik\grid\GridView::FILTER_DATE,
                        'filterWidgetOptions' => ([
                            'model'         => $searchModel,
                            'attribute'     => 'date',
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format'    => 'dd.mm.yyyy',
                            ]
                        ]),
                        'value'               => function ($data) {
                            return date('d.m.Y', strtotime($data->fd));
                        }
                    ],
                    [
                        'attribute'           => 'td',
                        'filterType'          => \kartik\grid\GridView::FILTER_DATE,
                        'filterWidgetOptions' => ([
                            'model'         => $searchModel,
                            'attribute'     => 'date',
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format'    => 'dd.mm.yyyy',
                            ]
                        ]),
                        'value'               => function ($data) {
                            return date('d.m.Y', strtotime($data->td));
                        }
                    ],
                    [
                        'attribute' => 'org',
                        'label'     => 'Организация MixCart',
                        'value'     => function ($model) {
                            if (isset($model))
                                return $model->organization ? $model->organization->name : null;

                        },
                    ],
                    [
                        'attribute' => 'code',
                        'filter'    => [1 => 'Стандартная лицензия', 2 => 'Расширенная лицензия'],
                        'value'     => function ($model) {
                            if ($model) return \api\common\models\merc\mercService::$licenses_list[$model->code];
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
