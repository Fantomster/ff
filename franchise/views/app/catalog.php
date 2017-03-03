<?php

use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;
use kartik\editable\Editable;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Category;
use common\models\CatalogBaseGoods;
use kartik\checkbox\CheckboxX;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
?>
<?php
/*
$this->registerJs("           
   // var uploadCrop;

function readFile(input) {
        if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
                        $('.upload-avatar').addClass('ready');
                        $('.upload-demo-wrap').css('opacity','1').css('z-index','198');
                        $('.upload-block').css('padding-bottom','44px');
                        console.log('ok');
        uploadCrop.croppie('bind', {
                url: e.target.result
        }).then(function(){
                console.log('jQuery bind complete');
        });

    }

    reader.readAsDataURL(input.files[0]);
}
else {
        swal('Sorry - your browser does not support the FileReader API');
    }
}
$(document).on('change', '#upload', function () { 
    size = $('#upload').get(0).files[0].size;
    if (size <= 2097152) {
        readFile(this); 
    }
});"
);*/
?>
<?php
$this->title = 'Каталог №' . $id;

$this->registerCss('');
?>
<?php
Modal::begin([
    'id' => 'add-product-market-place',
    'clientOptions' => false,
    'size' => 'modal-lg',
]);
Modal::end();
?>
<?php
$exportFilename = 'catalog_' . date("Y-m-d_H-m-s");
$exportColumns = [
    [
        'label' => 'Артикул',
        'value' => 'article',
    ],
    [
        'label' => 'Наименование',
        'value' => 'product',
    ],
    [
        'label' => 'Кратность',
        'value' => 'units',
    ],
    [
        'label' => 'Цена',
        'value' => 'price',
    ],
    [
        'label' => 'Единица измерения',
        'value' => 'ed',
    ],
    [
        'label' => 'Комментарий',
        'value' => function ($data) {
            return $data['note'] ? $data['note'] : '';
        },
    ]
        ]
?>
<?php
$grid = [
    [
        'attribute' => 'article',
        'label' => 'Артикул',
        'value' => 'article',
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'product',
        'label' => 'Наименование',
        'value' => 'product',
        'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
    ],
    [
        'attribute' => 'units',
        'label' => 'Кратность',
        'value' => function ($data) {
            return empty($data['units']) ? '' : $data['units'];
        },
        'contentOptions' => ['style' => 'vertical-align:middle;'],
    ],
    [
        'attribute' => 'category_id',
        'label' => 'Категория',
        'value' => function ($data) {
            $data['category_id'] == 0 ? $category_name = '' :
                            $category_name = \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name;
            return $category_name;
        },
                'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
                'attribute' => 'price',
                'label' => 'Цена',
                'value' => 'price',
                'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
                'attribute' => 'ed',
                'label' => 'Ед. измерения',
                'value' => function ($data) {
                    return $data['ed'];
                },
                'contentOptions' => ['style' => 'vertical-align:middle;'],
            ],
            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:40px'],
                'value' => function ($data) {
            $link = Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['/app/ajax-edit-catalog-form',
                        'product_id' => $data['id'], 'catalog' => $data['cat_id']], [
                        'data' => [
                            'target' => '#add-product-market-place',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                        'class' => 'btn btn-xs btn-default'
            ]);
            return $link;
        },
    ],
];
?> 
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Каталог № <?=$id?>
        <small></small>
    </h1>
</section>
<section class="content">
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <h4><i class="icon fa fa-check"></i>Ошибка</h4>
        <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>
    <div class="box box-info order-history">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group  pull-left">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        <?= Html::input('text', 'search', $searchString, ['class' => 'form-control', 'placeholder' => 'Поиск', 'id' => 'search', 'style'=>'width:300px']) ?>
                    </div>
                    
                    <?=
                    Modal::widget([
                        'id' => 'add-product',
                        'clientOptions' => ['class' => 'pull-right'],
                        'toggleButton' => [
                            'label' => '<i class="fa fa-plus-circle"></i> Новый товар',
                            'tag' => 'a',
                            'data-target' => '#add-product-market-place',
                            'class' => 'btn btn-fk-success btn-sm pull-right',
                            'href' => Url::to(['/app/ajax-edit-catalog-form', 'catalog' => $id]),
                        ],
                    ])
                    ?>
                    
                    <?=
                    Modal::widget([
                        'id' => 'importToXls',
                        'clientOptions' => false,
                        'size' => 'modal-md',
                        'toggleButton' => [
                            'label' => '<i class="glyphicon glyphicon-import"></i> <span class="text-label">Загрузить каталог (XLS)</span>',
                            'tag' => 'a',
                            'data-target' => '#importToXls',
                            'class' => 'btn btn-outline-default btn-sm pull-right',
                            'href' => Url::to(['/app/import-from-xls', 'id' => $id]),
                            'style' => 'margin-right:10px;',
                        ],
                    ])
                    ?>
                    <div class="pull-right">
                    <?=ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => $exportColumns,
                        'fontAwesome' => true,
                        'filename' => 'Главный каталог - ' . date('Y-m-d'),
                        'encoding' => 'UTF-8',
                        'target' => ExportMenu::TARGET_SELF,
                        'showConfirmAlert' => false,
                        'showColumnSelector' => false,
                        'dropdownOptions' => [
                            'label' => '<span class="text-label">Скачать каталог</span>',
                            'class' => ['btn btn-outline-default btn-sm'],
                            'style' => 'margin-right:10px;',
                        ],
                        'exportConfig' => [
                            ExportMenu::FORMAT_HTML => false,
                            ExportMenu::FORMAT_TEXT => false,
                            ExportMenu::FORMAT_EXCEL => false,
                            ExportMenu::FORMAT_PDF => false,
                            ExportMenu::FORMAT_CSV => false,
                            ExportMenu::FORMAT_EXCEL_X => [
                                'label' => Yii::t('kvexport', 'Excel'),
                                'icon' => 'file-excel-o',
                                'iconOptions' => ['class' => 'text-success'],
                                'linkOptions' => [],
                                'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                                'alertMsg' => Yii::t('kvexport', 'Файл EXCEL( XLSX ) будет генерироваться для загрузки'),
                                'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'extension' => 'xlsx',
                                'writer' => 'Excel2007',
                                'styleOptions' => [
                                    'font' => [
                                        'bold' => true,
                                        'color' => [
                                            'argb' => 'FFFFFFFF',
                                        ],
                                    ],
                                    'fill' => [
                                        'type' => PHPExcel_Style_Fill::FILL_NONE,
                                        'startcolor' => [
                                            'argb' => 'FFFFFFFF',
                                        ],
                                        'endcolor' => [
                                            'argb' => 'FFFFFFFF',
                                        ],
                                    ],
                                ]
                            ],
                        ],
                    ]);
                    ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    'pjax' => true,
                    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                    'filterPosition' => false,
                    'columns' => $grid,
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table table-bordered', 'role' => 'grid'],
                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'bordered' => false,
                    'striped' => false,
                    'condensed' => false,
                    'responsive' => false,
                    'hover' => false,
                    'resizableColumns' => false,
                    'export' => [
                        'fontAwesome' => true,
                    ],
                ]);
                ?>
                </div>
            </div>       
        </div>
    </div>  
</section>
<?php
$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        push: true,
        timeout: 10000,
        url: 'index.php?r=app/catalog&id=$id',
        container: '#kv-unique-id-1',
        data: {searchString: $('#search').val()}
      })
   }, 700);
});

$(document).on("click",".del-product", function(e){
    var id = $(this).attr('data-id');
	bootbox.confirm({
            title: "Удалить этот продукт?",
            message: "Продукт будет удален из всех каталогов", 
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
	        url: "index.php?r=app/ajax-delete-product",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
			        $.pjax.reload({container: "#kv-unique-id-1"}); 
			        }else{
				    console.log('Что-то пошло не так');    
			        }
		        }	
		    });
		}else{
		console.log('cancel');	
		}
	}});      
}) 
$("body").on("hidden.bs.modal", "#add-product-market-place", function() {
    $(this).data("bs.modal", null);
})
$("body").on("show.bs.modal", "#add-product-market-place", function() {
    $('#add-product-market-place>.modal-dialog').css('margin-top','13px');
})        
$(document).on("submit", "#marketplace-product-form", function(e) {
        e.preventDefault();
    var form = $("#marketplace-product-form");
    $('#loader-show').showLoading();
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $('#loader-show').hideLoading();
            form.replaceWith(result);
        $.pjax.reload({container: "#kv-unique-id-1"});
        });
        return false;
    });
  $('#add-product-market-place').removeAttr('tabindex');
JS;
        $this->registerJs($customJs, View::POS_READY);
        
