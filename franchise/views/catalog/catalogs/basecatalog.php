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
$this->title = Yii::t('app', 'franchise.views.catalog.catalogs.main_cat', ['ru'=>'Главный каталог']);

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
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.art_five', ['ru'=>'Артикул']),
        'value' => 'article',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.title', ['ru'=>'Наименование']),
        'value' => 'product',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.multiplicity_four', ['ru'=>'Кратность']),
        'value' => 'units',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.price_three', ['ru'=>'Цена']),
        'value' => 'price',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.measure_two', ['ru'=>'Единица измерения']),
        'value' => 'ed',
    ],
    [
        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.comment', ['ru'=>'Комментарий']),
        'value' => function ($data) {
            return $data['note'] ? $data['note'] : '';
        },
    ]
        ]
?>
<?php
Modal::begin([
    'header' => '<h4 class="modal-title">' . Yii::t('app', 'franchise.views.catalog.catalogs.download_catalog', ['ru'=>'Загрузка каталога']) . ' </h4>',
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
        <i class="fa fa-list-alt"></i> <?= Yii::t('app', 'franchise.views.catalog.catalogs.main_cat_two', ['ru'=>'Главный каталог']) ?>
        <small><?= Yii::t('app', 'franchise.views.catalog.catalogs.main_cat_three', ['ru'=>'Это ваш главный каталог']) ?></small><label>
            <div class="icheckbox_minimal-blue" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="checkbox" class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>
        </label>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'franchise.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            [
                'label' => Yii::t('app', 'franchise.views.catalog.catalogs.catalogs', ['ru'=>'Каталоги']),
                'url' => ['vendor/catalogs'],
            ],
            Yii::t('app', 'franchise.views.catalog.catalogs.main_cat_four', ['ru'=>'Главный каталог']),
        ],
    ])
    ?>
</section>
<section class="content">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4><i class="icon fa fa-check"></i><?= Yii::t('app', 'franchise.views.catalog.catalogs.error', ['ru'=>'Ошибка']) ?></h4>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="background-color: #f1f0ee;">
            <li class="active"><a data-toggle="tab" href="#tabCatalog"><h5 class="box-title"><?= Yii::t('app', 'franchise.views.catalog.catalogs.editing', ['ru'=>'Редактирование']) ?></h5></a></li>
            <li><a data-toggle="tab" href="#tabClients"><h5 class="box-title"><?= Yii::t('app', 'franchise.views.catalog.catalogs.set_to_rest', ['ru'=>'Назначить ресторану']) ?></h5></a></li>
        </ul>
        <div class="tab-content">
            <div id="tabCatalog" class="tab-pane fade in active">
                <div class="panel-body">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <?= Html::input('text', 'search', $searchString, ['class' => 'form-control pull-left', 'placeholder' => Yii::t('app', 'franchise.views.catalog.catalogs.search', ['ru'=>'Поиск']), 'id' => 'search']) ?>
                        </div>
                    </div>   
                    <?=
                    Modal::widget([
                        'id' => 'add-product',
                        'clientOptions' => ['style' => 'margin-top:13.2px;'],
                        'toggleButton' => [
                            'label' => '<i class="fa fa-plus-circle"></i> ' . Yii::t('app', 'franchise.views.catalog.catalogs.new_good_two', ['ru'=>'Новый товар']) . ' ',
                            'tag' => 'a',
                            'data-target' => '#add-product-market-place',
                            'class' => 'btn btn-fk-success btn-sm pull-right',
                            'href' => Url::to(['/vendor/ajax-create-product-market-place', 'id' => Yii::$app->request->get('id')]),
                        ],
                    ])
                    ?><div class="btn-group pull-right" placement="left" style="margin-right: 10px">
                    <?=
                    ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => $exportColumns,
                        'fontAwesome' => true,
                        'filename' => Yii::t('app', 'franchise.views.catalog.catalogs.main_cat_five', ['ru'=>'Главный каталог - ']) . date('Y-m-d'),
                        'encoding' => 'UTF-8',
                        'target' => ExportMenu::TARGET_SELF,
                        'showConfirmAlert' => false,
                        'showColumnSelector' => false,
                        'batchSize' => 200,
                        'timeout' => 0,
                        'dropdownOptions' => [
                            'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.catalog.catalogs.download_catalog_two', ['ru'=>'Скачать каталог']) . ' </span>',
                            'class' => ['btn btn-outline-default btn-sm pull-right']
                        ],
                        'exportConfig' => [
                            ExportMenu::FORMAT_HTML => false,
                            ExportMenu::FORMAT_TEXT => false,
                            ExportMenu::FORMAT_EXCEL => false,
                            ExportMenu::FORMAT_PDF => false,
                            ExportMenu::FORMAT_CSV => false, /* [
                              'label' => Yii::t('kvexport', 'CSV'),
                              'icon' => 'file-code-o',
                              'iconOptions' => ['class' => 'text-primary'],
                              'linkOptions' => [],
                              'options' => ['title' => Yii::t('kvexport', 'Comma Separated Values')],
                              'alertMsg' => Yii::t('kvexport', 'Вы загружаете CSV файл.'),
                              'mime' => 'application/csv;charset=UTF-8',
                              'extension' => 'csv',
                              'writer' => 'CSV'
                              ], */
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

                    <?=
                    Modal::widget([
                        'id' => 'importToXls',
                        'clientOptions' => false,
                        'size' => 'modal-md',
                        'toggleButton' => [
                            'label' => '<i class="glyphicon glyphicon-import"></i> <span class="text-label">' . Yii::t('app', 'franchise.views.catalog.catalogs.download_catalog_three', ['ru'=>'Загрузить каталог (XLS)']) . ' </span>',
                            'tag' => 'a',
                            'data-target' => '#importToXls',
                            'class' => 'btn btn-outline-default btn-sm pull-right',
                            'href' => Url::to(['/vendor/import-to-xls', 'id' => Yii::$app->request->get('id')]),
                            'style' => 'margin-right:10px;',
                        ],
                    ])
                    ?>
                    <?=
                    Html::a(
                            '<i class="fa fa-list-alt"></i> <span class="text-label">' . Yii::t('app', 'franchise.views.catalog.catalogs.download_template_three', ['ru'=>'Скачать шаблон (XLS)']) . ' </span>', Url::to('@web/upload/template.xlsx'), ['class' => 'btn btn-outline-default btn-sm pull-right', 'style' => ['margin-right' => '10px;']]
                    )
                    ?>
                    <?=
                    Html::a('<i class="fa fa-question-circle" aria-hidden="true"></i>', ['#'], [
                        'class' => 'btn btn-warning btn-sm pull-right',
                        'style' => 'margin-right:10px;',
                        'data' => [
                            'target' => '#instruction',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                    ]);
                    ?>
                </div>
                <div class="panel-body">
                    <?php
                    $gridColumnsBaseCatalog = [
                        [
                            'attribute' => 'article',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.art_six', ['ru'=>'Артикул']),
                            'value' => 'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'product',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.title', ['ru'=>'Наименование']),
                            'value' => 'product',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
                        ],
                        [
                            'attribute' => 'units',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.multiplicity_five', ['ru'=>'Кратность']),
                            'value' => function ($data) {
                                return empty($data['units']) ? '' : $data['units'];
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'category_id',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.category_two', ['ru'=>'Категория']),
                            'value' => function ($data) {
                                $data['category_id'] == 0 ? $category_name = '' : $category_name = \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name;
                                return $category_name;
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'price',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.price_four', ['ru'=>'Цена']),
                            'value' => 'price',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'ed',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.measure_three', ['ru'=>'Ед. измерения']),
                            'value' => function ($data) {
                                return $data['ed'];
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'status',
                            'label' => Yii::t('app', 'franchise.views.catalog.catalogs.in_stock_two', ['ru'=>'Наличие']),
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            'value' => function ($data) {
                                $link = CheckboxX::widget([
                                            'name' => 'status_' . $data['id'],
                                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                            'value' => $data['status'] == 0 ? 0 : 1,
                                            'autoLabel' => true,
                                            'options' => ['id' => 'status_' . $data['id'], 'data-id' => $data['id'], 'event-type' => 'set-status'],
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
                            'label' => 'F-MARKET',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'width:70px'],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => function ($data) {
                                $data['market_place'] == 0 ?
                                        $link = Html::a('<font style="font-weight:700;color:#555;">F</font>-MARKET', ['/vendor/ajax-update-product-market-place',
                                            'id' => $data['id']], [
                                            'data' => [
                                                'target' => '#add-product-market-place',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'class' => 'btn btn-sm btn-outline-success'
                                        ]) :
                                        $link = Html::a('<font style="font-weight:700;color:#555;">F</font>-MARKET', ['/vendor/ajax-update-product-market-place',
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
                        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.rest', ['ru'=>'Ресторан']),
                        'value' => function ($data) {
                            $organization_name = common\models\Organization::find()->where(['id' => $data->rest_org_id])->one()->name;
                            return $organization_name;
                        }
                    ],
                    [
                        'label' => Yii::t('app', 'franchise.views.catalog.catalogs.current_cat', ['ru'=>'Текущий каталог']),
                        'format' => 'raw',
                        'value' => function ($data) {
                            $catalog_name = $data->cat_id == 0 ? '' :
                                    common\models\Catalog::find()->where(['id' => $data->cat_id])->one()->name;
                            return $catalog_name;
                        }
                    ],
                    [
                        'attribute' => Yii::t('app', 'franchise.views.catalog.catalogs.set', ['ru'=>'Назначить']),
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
$baseCatalogUrl = Url::to(['vendor/basecatalog', 'id' => $currentCatalog]);
$changeCatalogPropUrl = Url::to(['vendor/changecatalogprop']);
$changeSetCatalogUrl = Url::to(['vendor/changesetcatalog']);
$deleteProductUrl = Url::to(['vendor/ajax-delete-product']);

$arr = [
    Yii::t('app', 'franchise.views.catalog.catalogs.var', ['ru'=>'Удалить этот продукт?']),
    Yii::t('app', 'franchise.views.catalog.catalogs.var1', ['ru'=>'Продукт будет удален из всех каталогов']),
    Yii::t('app', 'franchise.views.catalog.catalogs.var2', ['ru'=>'Удалить']),
    Yii::t('app', 'franchise.views.catalog.catalogs.var3', ['ru'=>'Отмена']),
    Yii::t('app', 'franchise.views.catalog.catalogs.var4', ['ru'=>'Что-то пошло не так']),
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
        data: {'id' : id, 'curCat' : $currentCatalog,'state' : state},
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
