<?php

use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\web\View;
use kartik\checkbox\CheckboxX;
use common\assets\CroppieAsset;
use common\models\Currency;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);

$currencyList = Json::encode(Currency::getList());
$currencySymbolList = Json::encode(Currency::getSymbolList());

$changeCurrencyUrl = Url::to(['vendor/ajax-change-currency', 'id' => $cat_id]);
$calculatePricesUrl = Url::to(['vendor/ajax-calculate-prices', 'id' => $cat_id]);
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
$this->title = 'Главный каталог';

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
Modal::begin([
    'header' => '<h4 class="modal-title">Загрузка каталога</h4>',
    'id' => 'instruction',
    'size' => 'modal-lg',
]);
echo '<iframe style="min-width: 320px;width: 100%;" width="854" height="480" id="video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen></iframe>';
Modal::end();
?>
<?php
Modal::begin([
    'id' => 'add-product-market-place',
    'clientOptions' => false,
    'size' => 'modal-lg',
]);
Modal::end();
?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Главный каталог
        <small>Это ваш главный каталог</small><label>
            <div class="icheckbox_minimal-blue" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="checkbox" class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>
        </label>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Каталоги',
                'url' => ['vendor/catalogs'],
            ],
            'Главный каталог',
        ],
    ])
    ?>
</section>
<section class="content">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4><i class="icon fa fa-check"></i>Ошибка</h4>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="background-color: #f1f0ee;">
            <li class="active"><a data-toggle="tab" href="#tabCatalog"><h5 class="box-title">Редактирование</h5></a></li>
            <li><a data-toggle="tab" href="#tabClients"><h5 class="box-title">Назначить ресторану</h5></a></li>
        </ul>
        <div class="tab-content">
            <div id="tabCatalog" class="tab-pane fade in active">
                <div class="panel-body">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <?= Html::input('text', 'search', $searchString, ['class' => 'form-control pull-left', 'placeholder' => 'Поиск', 'id' => 'search']) ?>
                        </div>
                    </div>   
                    <?=
                    Modal::widget([
                        'id' => 'add-product',
                        'clientOptions' => ['style' => 'margin-top:13.2px;'],
                        'toggleButton' => [
                            'label' => '<i class="fa fa-plus-circle"></i> Новый товар',
                            'tag' => 'a',
                            'data-target' => '#add-product-market-place',
                            'class' => 'btn btn-fk-success btn-sm pull-right',
                            'href' => Url::to(['/vendor/ajax-create-product-market-place', 'id' => $cat_id]),
                        ],
                    ])
                    ?>
                    <div class="btn-group pull-right" placement="left" style="margin-right: 10px">
                        <?=
                        ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => $exportColumns,
                            'fontAwesome' => true,
                            'filename' => 'Главный каталог - ' . date('Y-m-d'),
                            'encoding' => 'UTF-8',
                            'target' => ExportMenu::TARGET_SELF,
                            'showConfirmAlert' => false,
                            'showColumnSelector' => false,
                            'batchSize' => 200,
                            'timeout' => 0,
                            'dropdownOptions' => [
                                'label' => '<span class="text-label">экспорт</span>',
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

                    <?=
                    Modal::widget([
                        'id' => 'importToXls',
                        'clientOptions' => false,
                        'size' => 'modal-md',
                        'toggleButton' => [
                            'label' => '<i class="glyphicon glyphicon-import"></i> <span class="text-label">импорт</span>',
                            'tag' => 'a',
                            'data-target' => '#importToXls',
                            'class' => 'btn btn-outline-default btn-sm pull-right',
                            'href' => Url::to(['/vendor/import', 'id' => $cat_id]),
                            'style' => 'margin-right:10px;',
                        ],
                    ])
                    ?>
                    <?=
                    Html::button('<span class="text-label">Изменить валюту: </span> <span class="currency-symbol">' . $currentCatalog->currency->symbol . '</span>', [
                        'class' => 'btn btn-outline-default btn-sm pull-right',
                        'style' => ['margin-right' => '10px;'],
                        'id' => 'changeCurrency',
                    ])
                    ?>

                </div>
                <div class="panel-body">
                    <?php
                    $gridColumnsBaseCatalog = [
                        [
                            'attribute' => 'article',
                            'label' => 'Артикул',
                            'value' => 'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'product',
                            'label' => 'Наименование',
                            'format' => 'raw',
                            'value' => function($data) {
                                return Html::decode(Html::decode($data['product']));
                            },
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
                                $data['category_id'] == 0 ? $category_name = '' : $category_name = \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name;
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
                            'attribute' => 'status',
                            'label' => 'Наличие',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            'value' => function ($data) {
                                $link = CheckboxX::widget([
                                            'name' => 'status_' . $data['id'],
                                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                            'value' => $data['status'],
                                            'autoLabel' => true,
                                            'options' => ['id' => 'status_' . $data['id'], 'data-id' => $data['id'], 'event-type' => 'set-status', 'value' => $data['status']],
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
                        [
                            'attribute' => '',
                            'label' => 'MixMarket',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:70px'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function ($data) {
                                $data['market_place'] == 0 ?
                                        $res = '' :
                                        $res = '<center><i style="font-size: 28px;color:#84bf76;" class="fa fa-check-square-o"></i></center>';
                                return $res;
                            },
                        ],
                        [
                            'attribute' => '',
                            'label' => '',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:70px'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function ($data) {
                                $data['market_place'] == 0 ?
                                        $link = Html::a('ИЗМЕНИТЬ', ['/vendor/ajax-update-product-market-place',
                                            'id' => $data['id']], [
                                            'data' => [
                                                'target' => '#add-product-market-place',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'class' => 'btn btn-sm btn-outline-success'
                                        ]) :
                                        $link = Html::a('ИЗМЕНИТЬ', ['/vendor/ajax-update-product-market-place',
                                            'id' => $data['id']], [
                                            'data' => [
                                                'target' => '#add-product-market-place',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'class' => 'btn btn-sm btn-success'
                                ]);
                                return $link;
                            },
                        ],
                        [
                            'attribute' => '',
                            'label' => '',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:50px;'],
                            'value' => function ($data) {
                                $link = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                                            'class' => 'btn btn-sm btn-danger del-product',
                                            'data' => ['id' => $data['id']],
                                ]);
                                return $link;
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
                        'label' => 'Ресторан',
                        'value' => function ($data) {
                            $organization_name = common\models\Organization::find()->where(['id' => $data->rest_org_id])->one()->name;
                            return $organization_name;
                        }
                    ],
                    [
                        'label' => 'Текущий каталог',
                        'format' => 'raw',
                        'value' => function ($data) {
                            $catalog_name = $data->cat_id == 0 ? '' :
                                    common\models\Catalog::find()->where(['id' => $data->cat_id])->one()->name;
                            return $catalog_name;
                        }
                    ],
                    [
                        'attribute' => 'Назначить',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'width:50px;'],
                        'value' => function ($data) {
                            $value = ($data->cat_id == Yii::$app->request->get('id')) ? 1 : 0;
                            $link = CheckboxX::widget([
                                        'name' => 'setcatalog_' . $data->id,
                                        'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                        'value' => $value,
                                        'autoLabel' => true,
                                        'options' => ['id' => 'setcatalog_' . $data->id, 'data-id' => $data->rest_org_id, 'event-type' => 'set-catalog', 'value' => $value],
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
$baseCatalogUrl = Url::to(['vendor/basecatalog', 'id' => $currentCatalog->id]);
$changeCatalogPropUrl = Url::to(['vendor/changecatalogprop']);
$changeSetCatalogUrl = Url::to(['vendor/changesetcatalog']);
$deleteProductUrl = Url::to(['vendor/ajax-delete-product']);

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
        data: {'id' : id, 'curCat' : $currentCatalog->id,'state' : state},
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
$(".del-product").live("click", function(e){
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
	        url: "$deleteProductUrl",
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
    $("#btnSave").button("loading");
    $("#btnCancel").prop("disabled", true);
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $("#btnSave").button("reset");
            $("#btnCancel").prop("disabled", false);
            form.replaceWith(result);
        $.pjax.reload({container: "#kv-unique-id-1"});
        });
        return false;
    });
  $('#add-product-market-place').removeAttr('tabindex');

    var currencies = $.map($currencySymbolList, function(el) { return el });
    var newCurrency = {$currentCatalog->currency->id};
    var currentCurrency = {$currentCatalog->currency->id};
    var oldCurrency = {$currentCatalog->currency->id};
        
    $(document).on("click", "#changeCurrency", function() {
        swal({
            title: 'Изменение валюты каталога',
            input: 'select',
            inputOptions: $currencyList,
            inputPlaceholder: 'Выберите новую валюту каталога',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (!value) {
                        reject('Выберите валюту из списка')
                    }
                    if (value != currentCurrency) {
                        newCurrency = value;
                        resolve();
                    } else {
                        reject('Данная валюта уже используется!')
                    }
                })
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "{$changeCurrencyUrl}",
                        {newCurrencyId: newCurrency}
                    ).done(function (response) {
                        if (response.result === 'success') {
                            $(".currency-symbol").html(response.symbol);
                            oldCurrency = currentCurrency;
                            currentCurrency = newCurrency;
                            resolve();
                        } else {
                            swal({
                                type: response.result,
                                title: response.message
                            });
                        }
                    });
                })
            },
        }).then(function (result) {
            swal({
                title: 'Валюта каталога изменена!',
                type: 'success',
                html: 
                    '<hr /><div>Пересчитать цены в каталоге?</div>' +
                    '<input id="swal-curr1" class="swal2-input" style="width: 50px;display:inline;" value=1> ' + currencies[oldCurrency-1] + ' = ' +
                    '<input id="swal-curr2" class="swal2-input" style="width: 50px;display:inline;" value=1> ' + currencies[newCurrency-1],
                showCancelButton: true,
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                preConfirm: function () {
                    return new Promise(function (resolve) {
                        $.post(
                            '{$calculatePricesUrl}',
                            {oldCurrencyUnits: $('#swal-curr1').val(), newCurrencyUnits: $('#swal-curr2').val()}
                        ).done(function (response) {
                            if (response.result === 'success') {
                                $.pjax.reload({container: "#kv-unique-id-1", timeout:30000});
                                resolve();
                            } else {
                                swal({
                                    type: response.result,
                                    title: response.message
                                });
                            }
                        });
                    })
                }
            }).then(function (result) {
                swal({
                    type: "success",
                    title: "Цены успешно изменены!",
                    allowOutsideClick: true,
                });
            })
        })        
    });
        
JS;
$this->registerJs($customJs, View::POS_READY);
?>
