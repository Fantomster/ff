<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use kartik\grid\GridView;


?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с 1С Общепит
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            'Интеграция с 1С Общепит',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
</section>

<section class="content-header">
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
    ?>
    СПРАВОЧНИКИ:
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php Pjax::begin(['id' => 'dics_pjax']);
                    $columns = array (
                        [
                            'attribute' => 'dictype_id',
                            'value' => function ($model) {
                                return $model->dictype->denom;
                            },
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width: 10%;']
                        ],
                        'updated_at',
                        'obj_count',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'contentOptions' => ['style' => 'width: 6%;'],
                            'template' => '{view}&nbsp;&nbsp;&nbsp;{get}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return \yii\helpers\Html::a(
                                        '<i class="fa fa-eye" aria-hidden="true"></i>',
                                        Yii::$app->getUrlManager()->createUrl(['clientintegr/odinsobsh/default/' . $model->dictype->contr . '-view']),
                                        [
                                            'title' => Yii::t('backend', 'Просмотр'),
                                            'data-pjax' => "0"
                                        ]
                                    );
                                },
                            ]
                        ]
                    );
                    $timestamp_now=time();
                    if (!(($lic->status_id==1) && ($timestamp_now<=(strtotime($lic->td))))) {unset($columns[4]['buttons']['get']);}?>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'pjax' => false, // pjax is set to always true for this demo
                        'id' => 'dics_grid',
                        'filterPosition' => false,
                        'layout' => '{items}',
                        'columns' => $columns,
                        'options' => ['class' => 'table-responsive'],
                        'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                        'bordered' => false,
                        'striped' => true,
                        'condensed' => false,
                        'responsive' => false,
                        'hover' => true,
                        'resizableColumns' => false,
                        'export' => [
                            'fontAwesome' => true,
                        ],
                    ]);
                    ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

