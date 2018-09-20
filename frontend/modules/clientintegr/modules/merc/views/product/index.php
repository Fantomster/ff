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
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru'=>'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.client.integration.mercury', ['ru'=>'Интеграция с системой ВЕТИС "Меркурий"']),
        ],
    ])
    ?>
</section>
<section class="content">
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
    ?>
    <?php
    $timestamp_now=time();
    ($lic->status_id==1) && ($timestamp_now<=(strtotime($lic->td))) ? $lic_merc=1 : $lic_merc=0;
    $columns = array (
        /*[
            'class' => 'yii\grid\CheckboxColumn',
            'contentOptions'   =>   ['class' => 'small_cell_checkbox'],
            'headerOptions'    =>   ['style' => 'text-align:center; '],
            'checkboxOptions' => function($model, $key, $index, $widget) use ($searchModel){
                return ['value' => $model->uuid,'class'=>'checkbox-group_operations'];
            }
        ],*/
        [
            'attribute' => 'name',
            'label' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['name'];
            },
        ],
        [
            'attribute' => 'globalID',
            'label' => 'GTIN',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['globalID'];
            },
        ],
        [
            'attribute' => 'code',
            'label' => 'Артикул',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['code'];
            },
        ],
        [
            'attribute' => 'packagingType',
            'label' => 'Упаковка',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['packagingType'] ?? null;
            },
        ],
        [
            'attribute' => 'unit',
            'label' => 'Ед. Изм.',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['unit'] ?? null;
            },
        ],
        [
            'attribute' => 'createDate',
            'label' => 'Дата добавления',
            'format' => 'raw',
            'value' => function ($data) {
                return Yii::$app->formatter->asDatetime($data['createDate'], "php:j M Y");
            },
        ],
        [
            'attribute' => 'status',
            'label' => 'Статус',
            'format' => 'raw',
            'value' => function ($data) {
                return \api\common\models\merc\MercStockEntry::$statuses[$data['status']];
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'width: 7%;'],
            'template' => '{view}&nbsp;&nbsp;&nbsp;{update}&nbsp;&nbsp;&nbsp;{delete}',
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
                        'src'=>Yii::$app->request->baseUrl.'/img/view_vsd.png',
                        'style' => 'width: 16px'
                    ]);
                    return Html::a($icon, ['view', 'uuid' => $model->uuid], $options);
                },
                'update' =>  function ($url, $model) {
                    $customurl = Url::to(['update','uuid'=>$model->uuid]);
                    return \yii\helpers\Html::a( '<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                        ['title' => 'Редактировать позицию', 'data-pjax'=>"0"]);
                },
                'delete' =>  function ($url, $model) {
                    $customurl = Url::to(['delete','uuid'=>$model->uuid]);
                    return \yii\helpers\Html::a( '<i class="fa fa-trash" aria-hidden="true"></i>', $customurl,
                        ['title' => 'Удалить позицию', 'class' => 'del', 'data-pjax'=>"0", 'style'=>"color: #d9534f;"]);
                },
            ]
        ]
    );
    ?>
    <?= $this->render('/default/_menu.php', ['lic' => $lic]); ?>

    <h4><?= 'Справочники продукции' ?>:</h4>

    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php Pjax::begin(['id' => 'pjax-product-list', 'timeout' => 15000, 'scrollTo' => true, 'enablePushState' => false]); ?>
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
                    <?=
                    Html::a('<i class="fa fa-plus" style="margin-top:-3px;"></i><span class="hidden-sm hidden-xs"> Добавление новой продукции в номеклатуру </span>', ['create'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]);
                    ?>
                    <?php
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
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, "name", [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                ])
                                    ->textInput(['prompt' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), 'class' => 'form-control', 'id' => 'name'])
                                    ->label(Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']), ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, "globalID", [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                ])
                                    ->textInput(['prompt' => 'GTIN', 'class' => 'form-control', 'id' => 'globalID'])
                                    ->label('GTIN', ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, "code", [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                ])
                                    ->textInput(['prompt' => 'Артикул', 'class' => 'form-control', 'id' => 'code'])
                                    ->label('Артикул', ['class' => 'label search_string', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group field-statusFilter">
                                <?=
                                $form->field($searchModel, 'status')
                                    ->dropDownList(\api\common\models\merc\MercStockEntry::$statuses, ['id' => 'statusFilter','prompt'=>'Все'])
                                    ->label('Статус', ['class' => 'label', 'style' => 'color:#555'])
                                ?>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <?= Html::label( 'Дата добавления', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <div class="form-group" style="height: 44px;">
                                <?=
                                DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'from_create_date',
                                    'attribute2' => 'to_create_date',
                                    'options' => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'fromCreateDate'],
                                    'options2' => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'toCreateDate'],
                                    'separator' => '-',
                                    'type' => DatePicker::TYPE_RANGE,
                                    'pluginOptions' => [
                                        'orientation' => 'bottom left',
                                        'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                        'autoclose' => true,
                                        'endDate' => "0d",
                                    ]
                                ])
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 col-md-2 col-lg-1">
                            <?= Html::label('&nbsp;', null, ['class' => 'label']) ?>
                            <?= Html::button('<i class="fa fa-times" aria-hidden="true"></i>', ['class' => 'form-control clear_filters btn btn-outline-danger teaser']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end();?>
                    <div class="col-md-12">
                        <?php
                        //$checkBoxColumnStyle = ($searchModel->type == 2) ? "display: none;" : "";
                        echo GridView::widget([
                            'id' => 'vetStoreEntryList',
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            //'filterModel' => $searchModel,
                            //'filterPosition' => false,
                            'summary' => '',
                            'options' => ['class' => ''],
                            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                            'columns' => $columns
                        ]);
                        ?>
                    </div>
                    <?php Pjax::end(); ?>
                 </div>
            </div>
        </div>
    </div>
</section>

<?php
$loading = Yii::t('message', 'frontend.client.integration.loading', ['ru' => 'Загрузка']);
$urlCreateVSD = '';
$customJs = <<< JS
var justSubmitted = false;
$(document).on("click", ".create_vsd", function(e) {
        if($("#vetStoreEntryList").yiiGridView("getSelectedRows").length > 0){
            window.location.href =  "$urlCreateVSD?selected=" +  $("#vetStoreEntryList").yiiGridView("getSelectedRows");  
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
            $.pjax.reload("#pjax-product-list", {timeout:30000});
            if(result != true)    
                form.replaceWith(result);
            else
                $("#ajax-load .close").click();
        });
        return false;
    });

 $(document).on("click", ".clear_filters", function () {
           $('#name').val(''); 
           $('#globalID').val('');
           $('#fromCreateDate').val('');
           $('#toCreateDate').val('');
            $("#statusFilter").removeAttr("selected");
           $('#code').val('');
           $("#search-form").submit();
    });
 
 $(".box-body").on("change", "#fromCreateDate, #toCreateDate", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
 
 $(document).on("change keyup paste cut", "#name", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
  $(document).on("change keyup paste cut", "#globalID", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
    $(document).on("change keyup paste cut", "#code", function() {
     if (justSubmitted) {
            clearTimeout(justSubmitted);
        }
        justSubmitted = setTimeout(function() {
            justSubmitted = false;
            $("#search-form").submit();
        }, 700);
    });
  $("document").ready(function(){
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
     });
  $(document).on("click",".del", function(e){
      e.preventDefault();
        bootbox.confirm({
            title: "Удалить позицию?",
            message: "Позиция будет удалена из номенклатуры", 
            buttons: {
                confirm: {
                    label: 'Удалить',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Отмена',
                    className: 'btn-default'
                }
            },
            className: "danger-fk",
            callback: function(result) {
		if(result){
		$.ajax({
	        url: $(this).attr("href"),
	        type: "GET",
	        cache: false,
	        success: function(response) {
			       $.pjax.reload({container: "#pjax-product-list",timeout:30000});
		        }	
		    });
		}else{
		console.log('cancel');	
		}
	}});      
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>

