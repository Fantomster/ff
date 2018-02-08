<?php

use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;
use kartik\editable\Editable;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Category;
use common\models\CatalogBaseGoods;
use kartik\checkbox\CheckboxX;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
?>
<?php
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
                });
        "
);
?>
<?php
$this->title = Yii::t('app', 'franchise.views.catalog.main_catalog', ['ru'=>'Главный каталог']);

$this->registerCss('
@media (max-width: 1485px){
.text-label{
display:none;
}
}
@media (max-width: 1320px){
       th{
        min-width:110px;
        }
    }');
?>
<?=
Modal::widget([
    'id' => 'add-edit-product',
    'clientOptions' => false,
])
?>
<?php
$exportFilename = 'catalog_' . date("Y-m-d_H-m-s");
$exportColumns = [
    [
        'label' => Yii::t('app', 'franchise.views.catalog.art_three', ['ru'=>'Артикул']),
        'value' => 'article',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.name', ['ru'=>'Наименование']),
        'value' => 'product',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.multiplicity_two', ['ru'=>'Кратность']),
        'value' => 'units',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.price', ['ru'=>'Цена']),
        'value' => 'price',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.measure_two', ['ru'=>'Единица измерения']),
        'value' => 'ed',
    ],
//    [
//        'label' => Yii::t('app', 'franchise.views.catalog.comment', ['ru'=>'Комментарий']),
//        'value' => function ($data) {
//            return $data['note'] ? $data['note'] : '';
//        },
//    ]
        ]
?>


    <h1 style="padding-left: 30px; padding-top: 20px;">
         <?= $catalog->name ?>
    </h1>


<section class="content">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4><i class="icon fa fa-check"></i><?= Yii::t('app', 'franchise.views.catalog.error', ['ru'=>'Ошибка']) ?></h4>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <div class="nav-tabs-custom">
        <div class="tab-content">
            <div id="tabCatalog" class="tab-pane fade in active">
   <div class="btn-group" placement="left" style="margin-left: 10px">
                    <?=
                    ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => $exportColumns,
                        'fontAwesome' => true,
                        'filename' => Yii::t('message', 'market.views.site.supplier.catalog', ['ru'=>'КАТАЛОГ']) . " " . $catalog->name . " - " . date('Y-m-d'),
                        'encoding' => 'UTF-8',
                        'batchSize' => 200,
                        'timeout' => 0,
                        'target' => ExportMenu::TARGET_SELF,
                        'showConfirmAlert' => false,
                        'showColumnSelector' => false,
                        'dropdownOptions' => [
                            'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.catalog.download_cat', ['ru'=>'Скачать каталог']) . ' </span>',
                            'class' => ['btn btn-outline-default btn-sm pull-right']
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
                                //'writer' => 'Excel2007',
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
                <div class="panel-body">
                    <?php
                    $gridColumnsBaseCatalog = [
                        [
                            'attribute' => 'article',
                            'label' => Yii::t('app', 'franchise.views.catalog.art_four', ['ru'=>'Артикул']),
                            'value' => 'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'product',
                            'label' => Yii::t('app', 'franchise.views.catalog.name_two', ['ru'=>'Наименование']),
                            'value' => 'product',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
                        ],
                        [
                            'attribute' => 'units',
                            'label' => Yii::t('app', 'franchise.views.catalog.multiplicity_three', ['ru'=>'Кратность']),
                            'value' => function ($data) {
                                return empty($data['units']) ? '' : $data['units'];
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
//                        [
//                            'attribute' => 'category_id',
//                            'label' => Yii::t('app', 'franchise.views.catalog.category_two', ['ru'=>'Категория']),
//                            'value' => function ($data) {
//                                $data['category_id'] == 0 ? $category_name = '' : $category_name = Yii::t('app', \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name);
//                                return $category_name;
//                            },
//                            'contentOptions' => ['style' => 'vertical-align:middle;'],
//                        ],
                        [
                            'attribute' => 'price',
                            'label' => Yii::t('app', 'franchise.views.catalog.price', ['ru'=>'Цена']),
                            'value' => 'price',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'ed',
                            'label' => Yii::t('app', 'franchise.views.catalog.measure_three', ['ru'=>'Ед. измерения']),
                            'value' => function ($data) {
                                return $data['ed'];
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'status',
                            'label' => Yii::t('app', 'franchise.views.catalog.in_stock', ['ru'=>'Наличие']),
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            'value' => function ($data) {
                                $data['status'] == 0 ? '' : 1;
                                return $data['status'] == 0 ? '' : "<span class='fa fa-check'></span>";
                            },
                        ],
                    ];
                    ?>
                    <div class="panel-body">
                        <div class="box-body table-responsive no-padding">
                            <?=
                            GridView::widget([
                                'dataProvider' => $dataProvider,
                                'pjax' => true, // pjax is set to always true for this demo
                                'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                'filterPosition' => false,
                                'columns' => $gridColumnsBaseCatalog,
                                /* 'rowOptions' => function ($data, $key, $index, $grid) {
                                  return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                  }, */
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
                                'pager' => [
                                    'firstPageLabel' => true,
                                    'lastPageLabel' => true,
                                ],
                            ]);
                            ?>
                        </div>
                    </div>


                </div>

            </div>
            <div id="tabClients" class="tab-pane fade">
                <?php
                $gridColumnsCatalog = [
                    [
                        'label' => Yii::t('app', 'franchise.views.catalog.rest', ['ru'=>'Ресторан']),
                        'value' => function ($data) {
                            $organization_name = common\models\Organization::find()->where(['id' => $data->rest_org_id])->one()->name;
                            return $organization_name;
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'franchise.views.catalog.current_catalog', ['ru'=>'Текущий каталог']),
                        'format' => 'raw',
                        'value' => function ($data) {
                            $catalog = common\models\Catalog::get_value($data->cat_id);
                            $catalog_name = !empty($catalog->name) ? $catalog->name : '';
                            return $catalog_name;
                        }
                    ],
                    [
                        'attribute' => Yii::t('app', 'franchise.views.catalog.settle', ['ru'=>'Назначить']),
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'width:50px;'],
                        'value' => function ($data) {
                            $link = CheckboxX::widget([
                                        'name' => 'setcatalog_' . $data->id,
                                        'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                        'value' => $data->cat_id == Yii::$app->request->get('id') ? 1 : 0,
                                        'autoLabel' => true,
                                        'options' => ['id' => 'setcatalog_' . $data->id, 'data-id' => $data->rest_org_id, 'event-type' => 'set-catalog'],
                                        'pluginOptions' => [
                                            'threeState' => false,
                                            'theme' => 'krajee-flatblue',
                                            'enclosedLabel' => true,
                                            'size' => 'lg',
                                        ]
                            ]);
                            return $link;
                        },
                    ],
                ];
                ?>
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?php Pjax::begin(['enablePushState' => false, 'id' => 'clients-list',]); ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider2,
                            'filterModel' => $searchModel2,
                            'filterPosition' => false,
                            'columns' => $gridColumnsCatalog,
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'summary' => false,
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => false,
                        ]);
                        ?>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$baseCatalogUrl = Url::to(['catalog/basecatalog', 'vendor_id'=>$vendor_id, 'id' => $catalog->id]);
$changeCatalogPropUrl = Url::to(['catalog/changecatalogprop']);
$changeSetCatalogUrl = Url::to(['catalog/changesetcatalog', 'vendor_id'=>$vendor_id]);
$deleteProductUrl = Url::to(['catalog/ajax-delete-product', 'vendor_id'=>$vendor_id]);

$arr = [
    Yii::t('app', 'franchise.views.catalog.var', ['ru'=>'Удалить этот продукт?']),
    Yii::t('app', 'franchise.views.catalog.var1', ['ru'=>'Продукт будет удален из всех каталогов']),
    Yii::t('app', 'franchise.views.catalog.var2', ['ru'=>'Удалить']),
    Yii::t('app', 'franchise.views.catalog.var3', ['ru'=>'Отмена']),
    Yii::t('app', 'franchise.views.catalog.var4', ['ru'=>'Что-то пошло не так']),
];

$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        push: true,
        timeout: 10000,
        url: '$baseCatalogUrl',
        container: '#kv-unique-id-1',
        data: {searchString: $('#search').val()}
      })
   }, 700);
});
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == 'undefined' || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}     
//Статус продукта
$(document).on('click','input[event-type=set-status]', function(e) { 
    var id = $(this).attr('data-id');
    var state = $(this).prop("checked");
    var elem = $(this).attr('name').substr(0, 6);   
    $.ajax({
        url: "$changeCatalogPropUrl",
        type: "POST",
        dataType: "json",
        data: {'elem' : elem,'id' : id,'state' : state},
        cache: false,
        success: function(response) {
                console.log(response)
            },
            failure: function(errMsg) {
            console.log(errMsg);
        }
    });
});
//marketplace
$(document).on('change','input[event-type=marketplace]', function(e) {
    console.log('go')
    var id = $(this).attr('data-id');
    var state = $(this).prop("checked");
    var elem = $(this).attr('name').substr(0, 6);   
    $.ajax({
        url: "$changeCatalogPropUrl",
        type: "POST",
        dataType: "json",
        data: {'elem' : elem,'id' : id,'state' : state},
        cache: false,
        success: function(response) {
                console.log(response)
            },
            failure: function(errMsg) {
            console.log(errMsg);
        }
    });
});
//Назначить каталог
$(document).on('change','input[event-type=set-catalog]', function(e) {
    var id = $(this).attr('data-id');
    var state = $(this).prop("checked");
    var elem = $(this).attr('name').substr(0, 6);
    $.ajax({
        url: "$changeSetCatalogUrl",
        type: "POST",
        dataType: "json",
        data: {'id' : id, 'curCat' : $catalog->id,'state' : state},
        cache: false,
        success: function(response) {
                console.log(response)
                $.pjax.reload({container: "#clients-list"});
            },
            failure: function(errMsg) {
            console.log(errMsg);
        }
    });
})
$("body").on("hidden.bs.modal", "#add-product", function() {
    $(this).data("bs.modal", null);
    $.pjax.reload({container: "#products-list"});
})

$("#add-product").on("click", ".edit", function() {
    var form = $("#product-form");
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            form.replaceWith(result);
        });
        return false;
    });
$(document).on("click", ".del-product", function(e){
    var id = $(this).attr('data-id');
        
	bootbox.confirm({
            title: "$arr[0]",
            message: "$arr[1]", 
            buttons: {
                confirm: {
                    label: '$arr[2]',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$arr[3]',
                    className: 'btn-default'
                }
            },
            className: "danger-fk",
            callback: function(result) {
		if(result){
		$.ajax({
	        url: "$deleteProductUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
                        //$.pjax.reload({container: "#clients-list"});
			        $.pjax.reload({container: "#kv-unique-id-1"}); 
			        }else{
				    console.log('$arr[4]');    
			        }
		        }	
		    });
		}else{
		console.log('cancel');	
		}
	}});      
}) 
var url = $("#video").attr('src');        
$("#instruction").on('hide.bs.modal', function(){
$("#video").attr('src', '');
});
$("#instruction").on('show.bs.modal', function(){
$("#video").attr('src', url);
});
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
?>
