<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\widgets\DatePicker;
use api\common\models\merc\MercVsd;

common\assets\SweetAlertAsset::register($this);
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
        <?= Yii::t('message', 'frontend.client.integration.mercury', ['ru'=>'Интеграция с системой ВЕТИС "Меркурий"']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru' => 'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.client.integration.mercury', ['ru' => 'Интеграция с системой ВЕТИС "Меркурий"']),
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
    ?>
    <?php
    $checkBoxColumnStyle = ($searchModel->type == 2) ? "display: none;" : "";
    $timestamp_now = time();
    ($lic->status_id == 1) && ($timestamp_now <= (strtotime($lic->td))) ? $lic_merc = 1 : $lic_merc = 0;
    $columns = array(
        [
            'class' => 'yii\grid\CheckboxColumn',
            'contentOptions' => ['class' => 'small_cell_checkbox', 'style' => $checkBoxColumnStyle],
            'headerOptions' => ['style' => 'text-align:center; ' . $checkBoxColumnStyle],
            'checkboxOptions' => function($model, $key, $index, $widget) use ($searchModel) {
                $enable = !($model->status == MercVsd::DOC_STATUS_CONFIRMED) || $searchModel->type == 2;
                $style = ($enable ) ? "visibility:hidden" : "";
                return ['value' => $model->uuid, 'class' => 'checkbox-group_operations', 'disabled' => $enable, 'readonly' => $enable, 'style' => $style];
            }
        ],
        /* [
          'attribute' => 'number',
          'format' => 'raw',
          'value' => function ($data) {
          return $data['number'];
          },
          ], */
        [
            'attribute' => 'date_doc',
            'label' => Yii::t('message', 'frontend.client.integration.date_doc', ['ru' => 'Дата оформления']),
            'format' => 'raw',
            'value' => function ($data) {
                return Yii::$app->formatter->asDatetime($data['date_doc'], "php:j M Y");
            },
        ],
        [
            'attribute' => 'status',
            'label' => Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']),
            'format' => 'raw',
            'value' => function ($data) {
                return '<span class="status ' . MercVsd::$status_color[$data['status']] . '">' . MercVsd::$statuses[$data['status']] . '</span>';
            },
        ],
        [
            'attribute' => 'product_name',
            'label' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['product_name'];
            },
        ],
        [
            'attribute' => 'amount',
            'label' => Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объём']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['amount'] . " " . $data['unit'];
            },
        ],
        [
            'attribute' => 'production_date',
            'label' => Yii::t('message', 'frontend.client.integration.created_at', ['ru' => 'Дата изготовления']),
            'format' => 'raw',
            'value' => function ($data) {
                $res = $data['production_date'];
                try {
                    $res = Yii::$app->formatter->asDatetime($data['production_date'], "php:j M Y");
                } catch (Exception $e) {
                    $res = $data['production_date'];
                }
                return $res;
            },
        ],
        [
            'attribute' => 'sender_name',
            'label' => Yii::t('message', 'frontend.client.integration.recipient', ['ru' => 'Фирма-отправитель']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['sender_name'];
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 7%;'],
            'template' => '{view}&nbsp;&nbsp;{pdf}&nbsp;&nbsp;{done-partial}&nbsp;&nbsp;{rejected}',
            'buttons' => [
                'view' => function ($url, $model, $key) use ($lic_merc) {
                    $options = [
                        'title' => Yii::t('message', 'frontend.client.integration.view', ['ru' => 'Просмотр']),
                        'aria-label' => Yii::t('message', 'frontend.client.integration.view', ['ru' => 'Просмотр']),
                        'data' => [
                            //'pjax'=>0,
                            'target' => '#ajax-load',
                            'toggle' => 'modal',
                            'backdrop' => 'static'
                        ],
                            //'data-pjax' => '0',
                    ];
                    $icon = Html::tag('img', '', [
                                'src' => Yii::$app->request->baseUrl . '/img/view_vsd.png',
                                'style' => 'width: 16px'
                    ]);
                    return Html::a($icon, ['view', 'uuid' => $model->uuid], $options);
                },
                'done-partial' => function ($url, $model, $key) use ($searchModel) {
                    if ($model->status != MercVsd::DOC_STATUS_CONFIRMED || $searchModel->type == 2)
                        return "";
                    $options = [
                        'title' => Yii::t('message', 'frontend.client.integration.done_partial', ['ru' => 'Частичная приёмка']),
                        'aria-label' => Yii::t('message', 'frontend.client.integration.done_partial', ['ru' => 'Частичная приёмка']),
                        'data' => [
                            //'pjax'=>0,
                            'target' => '#ajax-load',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                    ];
                    $icon = Html::tag('img', '', [
                                'src' => Yii::$app->request->baseUrl . '/img/partial_confirmed.png',
                                'style' => 'width: 24px'
                    ]);
                    return Html::a($icon, ['done-partial', 'uuid' => $model->uuid], $options);
                },
                'rejected' => function ($url, $model, $key) use ($searchModel) {
                    if ($model->status != MercVsd::DOC_STATUS_CONFIRMED || $searchModel->type == 2)
                        return "";
                    $options = [
                        'title' => Yii::t('message', 'frontend.client.integration.return_all', ['ru' => 'Возврат']),
                        'aria-label' => Yii::t('message', 'frontend.client.integration.return_all', ['ru' => 'Возврат']),
                        'data' => [
                            //'pjax'=>0,
                            'target' => '#ajax-load',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                    ];
                    $icon = Html::tag('img', '', [
                                'src' => Yii::$app->request->baseUrl . '/img/back_vsd.png',
                                'style' => 'width: 18px'
                    ]);
                    return Html::a($icon, ['done-partial', 'uuid' => $model->uuid, 'reject' => true], $options);
                },
                'pdf' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-file-pdf-o" aria-hidden="true"></i>', '#', [
                        'data-pjax' => 0, 
                        'class' => 'download-pdf', 
                        'data-url' => Url::to(['get-pdf', 'uuid' => $model->uuid]),
                        'data-name' => str_replace(' ', '_', $model->product_name . '_' . Yii::$app->formatter->asDatetime($model->date_doc, "php:j M Y") . '.pdf'),
                        'title' => Yii::t('message', 'frontend.client.integration.pdf', ['ru' => 'Загрузка PDF']),
                        ]);
                },
            ]
        ]
    );
    if ($lic_merc == 0) {
        unset($columns[7]['buttons']['done-partial']);
        unset($columns[7]['buttons']['rejected']);
    }
    ?>
</section>
<?php
$user = Yii::$app->user->identity;
if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) {
echo "<section class=\"content-header\">";
echo $this->render('/default/_menu.php');
echo "</section>";
} ?>
<section class="content-header">
    <h4><?= Yii::t('message', 'frontend.client.integration.mercury.vsd_list', ['ru' => 'Список ВСД"']) ?>:</h4>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php Pjax::begin(['id' => 'pjax-vsd-list', 'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => false]); ?>
<?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.client.integration.mercury.successful', ['ru' => 'Выполнено']) ?>
                            </h4>
                        <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <h4>
                                <i class="icon fa fa-exclamation-circle"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                            </h4>
                        <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <?= Html::button('<i class="fa fa-upload"></i> ' . Yii::t('app', 'frontend.client.integration.mercury.hand_loading', ['ru' => 'Ручная загрузка ВСД']), ['class' => 'btn btn-success hand_loading']) ?>
                    <?php
                    $searchModel->status = isset($searchModel->status) ? $searchModel->status : MercVsd::DOC_STATUS_CONFIRMED;
                    $form = ActiveForm::begin([
                                'options' => [
                                    'data-pjax' => true,
                                    'id' => 'search-form',
                                    'role' => 'search',
                                ],
                                'enableClientValidation' => false,
                                'method' => 'get',
                    ]);
                    ?>
                    <div class="col-md-12">
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                        $form->field($searchModel, 'type')
                                        ->dropDownList([1 => 'Входящие', 2 => 'Исходящие'], ['id' => 'typeFilter'], ['options' =>
                                            [
                                                1 => ['selected' => true]
                                            ]
                                        ])
                                        ->label(Yii::t('message', 'frontend.views.order.type', ['ru' => 'Статус']), ['class' => 'label', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                        $form->field($searchModel, 'status')
                                        ->dropDownList(MercVsd::$statuses, ['id' => 'statusFilter'])
                                        ->label(Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']), ['class' => 'label', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 col-md-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                        $form->field($searchModel, 'sender_name')
                                        ->dropDownList($searchModel->getRecipientList(), ['id' => 'recipientFilter'])
                                        ->label(Yii::t('message', 'frontend.client.integration.recipient', ['ru' => 'Фирма-отравитель']), ['class' => 'label', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                                <?= Html::label(Yii::t('message', 'frontend.views.order.begin_end', ['ru' => 'Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <div class="form-group" style="height: 44px;">
                                <?=
                                DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'date_from',
                                    'attribute2' => 'date_to',
                                    'options' => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'dateFrom'],
                                    'options2' => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'dateTo'],
                                    'separator' => '-',
                                    'type' => DatePicker::TYPE_RANGE,
                                    'pluginOptions' => [
                                        'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                        'autoclose' => true,
                                        'endDate' => "0d",
                                    ]
                                ])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                        $form->field($searchModel, "product_name", [
                                            'addon' => [
                                                'append' => [
                                                    'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                                    'options' => [
                                                        'class' => 'append',
                                                    ],
                                                ],
                                            ],
                                        ])
                                        ->textInput(['prompt' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), 'class' => 'form-control', 'id' => 'product_name'])
                                        ->label(Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 col-md-2 col-lg-1">
<?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                    <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                        </div>
                    </div>
                        <?php ActiveForm::end(); ?>
                    <div class="col-md-12">
                        <?php
                        $checkBoxColumnStyle = ($searchModel->type == 2) ? "display: none;" : "";
                        echo GridView::widget([
                            'id' => 'vetDocumentsList',
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            //'filterModel' => $searchModel,
                            //'filterPosition' => false,
                            'summary' => '',
                            'options' => ['class' => ''],
                            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                            'columns' => $columns
                        ]);
                        if ($lic_merc == 1) {
                            if ($searchModel->type != 2 && ($searchModel->status == 'CONFIRMED' || $searchModel->status == null))
                                echo '<div class="col-md-12">' . Html::submitButton(Yii::t('message', 'frontend.client.integration.done', ['ru' => 'Погасить']), ['class' => 'btn btn-success done_all']) . '</div>';
                        }
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
$loading = Yii::t('message', 'frontend.client.integration.loading', ['ru' => 'Загрузка']);
$title = Yii::t('message', 'frontend.client.integration.hand_loading', ['ru' => 'Список ВСД для загрузки']);
$cancelButtonText = Yii::t('message', 'frontend.views.order.close_three', ['ru' => 'Закрыть']);
$confirmButtonText = Yii::t('message', 'frontend.client.integration.load', ['ru' => 'Загрузить']);
$error = Yii::t('error', 'frontend.views.order.error_four', ['ru' => 'Ошибка!']);
$error_text = Yii::t('message', 'frontend.views.order.try_again_four', ['ru' => 'Попробуйте еще раз']);
$preparePdfText = Yii::t('message', 'frontend.client.integration.pdf_prepare', ['ru' => 'Формируем PDF...']);
$loadUrl = Url::to(['ajax-load-vsd']);
$customJs = <<< JS
var justSubmitted = false;
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
    "</span><h4 class=\"modal-title\"><span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span>$loading</h4></div>");
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
            $.pjax.reload("#pjax-vsd-list", {timeout:30000});
            if(result != true)    
                form.replaceWith(result);
            else
                $("#ajax-load .close").click();
        });
        return false;
    });

 $("document").ready(function(){
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
     }); 
 
  $("document").ready(function(){
        $(".box-body").on("change", "#typeFilter", function() {
            $("#search-form").submit();
        });
     });  
 
 $("document").ready(function(){
        $(".box-body").on("change", "#recipientFilter", function() {
            $("#search-form").submit();
        });
     });   
 
 $(document).on("click", ".clear_filters", function () {
           $('#product_name').val(''); 
           $('#statusFilter').val(''); 
           $('#typeFilter').val('1');
           $('#dateFrom').val('');
           $('#dateTo').val('');
           $('#recipientFilter').val('');
           $("#search_form").submit();
    });
 
 $(".box-body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        }); 
 
 $(document).on("change keyup paste cut", "#product_name", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
 
$(document).on("click", ".hand_loading", function(e) {
                e.preventDefault();
                var clicked = $(this);
                    title = "$title";
                swal({
                    title: title,
                    input: "textarea",
                    showCancelButton: true,
                    cancelButtonText: "$cancelButtonText",
                    confirmButtonText: "$confirmButtonText",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    showLoaderOnConfirm: true,
                    inputValue: "",
                    onClose: function() {
                        clicked.blur();
                        swal.resetDefaults()
                    },
                    preConfirm: function (text) {
                        return new Promise(function (resolve, reject) {
                            $.post(
                                "$loadUrl",
                                {list: text}
                            ).done(function (result) {
                                if (result) {
                                    resolve(result);
                                } else {
                                    resolve(false);
                                }
                            });
                        })
                    },
                }).then(function (result) {
                    if (result.value.type == "success") {
                        clicked.tooltip("hide")
                            .attr("data-original-title", result.value.comment)
                            .tooltip("fixTitle")
                            .blur();
                        clicked.data("original-title", result.value.comment);
                        swal(result.value);
                    } else if (result.dismiss === "cancel") {
                        swal.close();
                        $.pjax.reload("#pjax-vsd-list", {timeout:30000});
                    } else {
                        swal({title: "$error", text: "$error_text", type: "error"});
                    }
                });
            }); 
    $(document).on('click', '.download-pdf', function(e) {
        e.preventDefault();
        url = $(this).data('url');
        filename = $(this).data('name');
        swal({
            title: '$preparePdfText'
        });
        swal.showLoading();
        $.ajax({
            url: url,
            method: 'GET',
            xhrFields: {
                responseType: 'blob'
            },
            success: function (data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                document.body.appendChild(a);
                a.href = url;
                a.download = filename;
                a.class = "pdf-download";
                a.click();
                window.URL.revokeObjectURL(url);
                swal.close()
            }
        });
    });
JS;
$this->registerJs($customJs, View::POS_READY);
?>