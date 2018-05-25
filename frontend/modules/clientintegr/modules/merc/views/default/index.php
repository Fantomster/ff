<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\helpers\Url;
use kartik\form\ActiveForm;
?>

<?=
Modal::widget([
    'id' => 'ajax-load',
    'size' => 'modal-md',
    'clientOptions' => false,
])
?>

<section class="content-header">
    <h1>
        <img src="<?= Yii::$app->request->baseUrl ?>/img/mercuriy_icon.png" style="width: 32px;">
        Интеграция с системой ВЕТИС "Меркурий"
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
    <h4>Список ВСД:</h4>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                            </h4>
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    Pjax::begin(['id' => 'pjax-vsd-list', 'enablePushState' => true,'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => false]);
                    $form = ActiveForm::begin([
                    'options' => [
                    'data-pjax' => true,
                    'id' => 'search-form',
                    'role' => 'search',
                    ],
                    'enableClientValidation' => false,
                    'method' => 'get',
                    ]); ?>
                    <div class="col-md-12">
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <div class="form-group field-statusFilter">
                                <label class="label" style="color:#555" for="statusFilter">Статус</label>
                                <?= Html::dropDownList('status', $status, \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList::$statuses, ['class' => 'form-control', 'id'=>'statusFilter']); ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
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
                                'contentOptions' => ['style' => 'width: 7%;'],
                                'template' => '{view}&nbsp;&nbsp;&nbsp;{done-partial}&nbsp;&nbsp;&nbsp;{rejected}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        $options = [
                                            'title' => 'Просмотр',
                                            'aria-label' => 'Просмотр',
                                            'data' => [
                                               //'pjax'=>0,
                                                'target' => '#ajax-load',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            //'data-pjax' => '0',
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
                                            'title' => 'Частичная приемка',
                                            'aria-label' => 'Частичная приемка',
                                            'data' => [
                                                //'pjax'=>0,
                                                'target' => '#ajax-load',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                        ];
                                         $icon = Html::tag('img', '', [
                                                'src'=>Yii::$app->request->baseUrl.'/img/partial_confirmed.png',
                                                'style' => 'width: 24px'
                                            ]);
                                        return Html::a($icon, ['done-partial', 'uuid' => $key], $options);
                                    },
                                    'rejected' => function ($url, $model, $key) {
                                        if ($model['status_raw'] != \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList::DOC_STATUS_CONFIRMED)
                                            return "";
                                        $options = [
                                            'title' => 'Возврат',
                                            'aria-label' => 'Возврат',
                                            'data' => [
                                                //'pjax'=>0,
                                                'target' => '#ajax-load',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                        ];
                                        $icon = Html::tag('img', '', [
                                            'src'=>Yii::$app->request->baseUrl.'/img/back_vsd.png',
                                            'style' => 'width: 18px'
                                        ]);
                                        return Html::a($icon, ['done-partial', 'uuid' => $key,  'reject' => true], $options);
                                    },
                                ]
                            ]
                        ],
                    ]);
                    echo '<div class="col-md-12">'.Html::submitButton('Погасить', ['class' => 'btn btn-success done_all']).'</div>';
                    ?>
                    </div>
                    <?php Pjax::end(); ?>
                  </div>
            </div>
        </div>
    </div>
</section>

<?php
$urlDoneAll = Url::to(['done-all']);
$customJs = <<< JS
$(document).on("click", ".done_all", function(e) {
        if($("#vetDocumentsList").yiiGridView("getSelectedRows").length > 0){
            window.location.href =  "$urlDoneAll?selected=" +  $("#vetDocumentsList").yiiGridView("getSelectedRows");  
        }
    });

$("body").on("show.bs.modal", "#ajax-load", function() {
    $(this).data("bs.modal", null);
    var modal = $(this);
    modal.find('.modal-content').html(
    "<div class=\"modal-header\">" + 
    "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>" + 
    "</span><h4 class=\"modal-title\"><span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Загрузка</h4></div>");
});


$(".modal").removeAttr("tabindex");

$("body").on("hidden.bs.modal", "#ajax-load", function() {
    $(this).data("bs.modal", null);
});

$("#ajax-load").on("click", ".save-form", function() {
    var form = $("#ajax-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $.pjax.reload({container: "#pjax-vsd-list",timeout:30000});
            if(result != true)    
                form.replaceWith(result);
            else
                //$("#ajax-load").modal('hide');
                $("#ajax-load .close").click();
                //$('#ajax-load').modal().hide();
        });
        return false;
    });

 $("document").ready(function(){
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
     });   
JS;
$this->registerJs($customJs, View::POS_READY);
?>

