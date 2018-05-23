<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с системой ВЕТИС "Меркурий"
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
            'Интеграция с системой ВЕТИС "Меркурий"',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
</section>

<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') '; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <h4>Список ВСД:</h4>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php
                    Pjax::begin(['id' => 'pjax-messages-list', 'enablePushState' => true,'timeout' => 15000, 'scrollTo' => true]);
                    ?>
                    <div class="col-md-12">
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <div class="form-group field-statusFilter">
                                <label class="label" style="color:#555" for="statusFilter">Статус</label>
                                <?= Html::dropDownList('status', 'null', \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList::$statuses, ['class' => 'form-control']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                    <?php
                    echo GridView::widget([
                        'id' => 'vetDocumentsList',
                        'dataProvider' => $dataProvider,
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        //'filterModel' => $searchModel,
                        //'filterPosition' => false,
                        'summary' => '',
                        'options' => ['class' => ''],
                        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                'contentOptions'   =>   ['class' => 'small_cell_checkbox'],
                                'headerOptions'    =>   ['style' => 'text-align:center;'],
                                'checkboxOptions' => function($model, $key, $index, $widget){
                                    $enable = !($model['status_raw'] == \frontend\modules\clientintegr\modules\merc\models\getVetDocumentListRequest::DOC_STATUS_CONFIRMED);
                                    $style = ($enable) ? "visibility:hidden" : "";
                                    return ['value' => $model['uuid'],'class'=>'checkbox-group_operations', 'disabled' => $enable, 'readonly' => $enable, 'style' => $style ];
                                }
                            ],
                            /*[
                                'attribute' => 'number',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['number'];
                                },
                            ],*/
                            [
                                'attribute' => 'date_doc',
                                'label' => 'Дата оформления',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Yii::$app->formatter->asDatetime($data['date_doc'], "php:j M Y");
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Статус',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['status'];
                                },
                            ],
                            [
                                'attribute' => 'product_name',
                                'label' => 'Наименование продукции',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['product_name'];
                                },
                            ],
                            [
                                'attribute' => 'amount',
                                'label' => 'Объем',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['amount'];
                                },
                            ],
                            [
                                'attribute' => 'production_date',
                                'label' => 'Дата изготовления',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Yii::$app->formatter->asDatetime($data['production_date'], "php:j M Y");
                                },
                            ],
                            [
                                'attribute' => 'recipient_name',
                                'label' => ' 	Фирма-отправитель',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['recipient_name'];
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'contentOptions' => ['style' => 'width: 6%;'],
                                'template' => '{view}&nbsp;&nbsp;&nbsp;{done-partial}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        $options = [
                                            'title' => 'Просмотр',
                                            'aria-label' => 'Просмотр',
                                            'data-pjax' => '0',
                                        ];
                                        $icon = Html::tag('img', '', [
                                            'src'=>Yii::$app->request->baseUrl.'/img/view_vsd.png',
                                            'style' => 'width: 16px'
                                        ]);
                                        return Html::a($icon, ['view', 'uuid' => $key], $options);
                                    },
                                    'done-partial' => function ($url, $model, $key) {
                                        if ($model['status_raw'] != \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList::DOC_STATUS_CONFIRMED)
                                            return "";
                                         $options = [
                                                'title' => 'Частичня приемка',
                                                'aria-label' => 'Частичня приемка',
                                                'data-pjax' => '0',
                                            ];
                                         $icon = Html::tag('img', '', [
                                                'src'=>Yii::$app->request->baseUrl.'/img/partial_confirmed.png',
                                                'style' => 'width: 24px'
                                            ]);
                                        return Html::a($icon, ['view', 'uuid' => $key], $options);
                                    },
                                ]
                            ]
                        ],
                    ]);
                    echo '<div class="col-md-12">'.Html::a('Погасить', ['#'], ['class' => 'btn btn-success']).' '.
                         Html::a('Вернуть', ['#'], ['class' => 'btn btn-danger']).'</div>';
                    ?>
                    </div>
                    <?php Pjax::end(); ?>
                  </div>
            </div>
        </div>
    </div>
</section>
