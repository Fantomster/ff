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

$currencyList             = Json::encode(Currency::getList());
$currencySymbolList       = Json::encode(Currency::getSymbolList());
$indexesList              = Json::encode(\common\models\Catalog::getMainIndexesList());
$titleDeleteAll           = Yii::t('message', 'frontend.views.vendor.delete_all', ['ru' => 'Удалить все']);
$cancelText               = Yii::t('message', 'frontend.views.vendor.cancel_eleven', ['ru' => 'Отмена']);
$catalogDeleted           = Yii::t('message', 'frontend.views.vendor.catalog_deleted', ['ru' => 'Каталог удален!']);
$indexChanged             = Yii::t('message', 'frontend.views.vendor.index_changed', ['ru' => 'Индекс изменен!']);
$catalogNotEmpty          = Yii::t('message', 'frontend.views.vendor.catalog_not_empty', ['ru' => 'Каталог не пустой']);
$catalogDeletionFailed    = Yii::t('message', 'frontend.views.vendor.catalog_deletion_failed', ['ru' => 'Не удалось удалить каталог']);
$buttonDeleteRestore      = Yii::t('message', 'frontend.views.vendor.btn_delete_restore', ['ru' => 'Удалить/восстановить каталог']);
$buttonDelete             = Yii::t('message', 'frontend.views.vendor.btn_delete', ['ru' => 'Удалить каталог']);
$buttonRestore            = Yii::t('message', 'frontend.views.vendor.btn_restore', ['ru' => 'Восстановить последнюю сохраненную копию каталога']);
$titleRestoreCatalog      = Yii::t('message', 'frontend.views.vendor.restore_catalog', ['ru' => 'Восстановить каталог']);
$catalogRestorationFailed = Yii::t('message', 'frontend.views.vendor.catalog_restoration_failed', ['ru' => 'Не удалось восстановить каталог']);
$catalogRestored          = Yii::t('message', 'frontend.views.vendor.catalog_restored', ['ru' => 'Каталог восстановлен!']);

$changeCurrencyUrl  = Url::to(['vendor/ajax-change-currency', 'id' => $cat_id]);
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
$this->title        = Yii::t('message', 'frontend.views.vendor.main_catalog_two', ['ru' => 'Главный каталог']);

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
    'id'            => 'add-edit-product',
    'clientOptions' => false,
])
?>
<?php
$exportFilename = 'catalog_' . date("Y-m-d_H-m-s");
$exportColumns  = [
    [
        'label' => Yii::t('message', 'frontend.views.vendor.art_five', ['ru' => 'Артикул']),
        'value' => 'article',
    ],
    [
        'label' => Yii::t('message', 'frontend.views.vendor.name_of_good', ['ru' => 'Наименование']),
        'value' => function ($data) {
            return Html::decode(Html::decode($data['product']));
        },
    ],
    [
        'label' => Yii::t('message', 'frontend.views.vendor.multiplicity_three', ['ru' => 'Кратность']),
        'value' => 'units',
    ],
    [
        'label' => Yii::t('message', 'frontend.views.vendor.price_four', ['ru' => 'Цена']),
        'value' => 'price',
    ],
    [
        'label' => Yii::t('message', 'frontend.views.vendor.measure_two', ['ru' => 'Единица измерения']),
        'value' => function ($data) {
            return Yii::t('app', $data['ed']);
        },
    ],
    [
        'label' => Yii::t('message', 'frontend.views.vendor.comment', ['ru' => 'Комментарий']),
        'value' => function ($data) {
            return $data['note'] ? $data['note'] : '';
        },
    ]
        ]
?>
<?php
Modal::begin([
    'header' => '<h4 class="modal-title">' . Yii::t('message', 'frontend.views.vendor.downl_cat', ['ru' => 'Загрузка каталога']) . ' </h4>',
    'id'     => 'instruction',
    'size'   => 'modal-lg',
]);
echo '<iframe style="min-width: 320px;width: 100%;" width="854" height="480" id="video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen></iframe>';
Modal::end();
?>
<?php
Modal::begin([
    'id'            => 'add-product-market-place',
    'clientOptions' => false,
    'size'          => 'modal-lg',
]);
Modal::end();
?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.main_catalog_three', ['ru' => 'Главный каталог']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.main_catalog_four', ['ru' => 'Это ваш главный каталог']) ?></small>
        <label>
            <div class="icheckbox_minimal-blue" aria-checked="false" aria-disabled="false" style="position: relative;">
                <input type="checkbox" class="minimal" style="position: absolute; opacity: 0;">
                <ins class="iCheck-helper"
                     style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins>
            </div>
        </label>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options'  => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links'    => [
            [
                'label' => Yii::t('message', 'frontend.views.vendor.catalogs_four', ['ru' => 'Каталоги']),
                'url'   => ['vendor/catalogs'],
            ],
            Yii::t('message', 'frontend.views.vendor.main_catalog_five', ['ru' => 'Главный каталог']),
        ],
    ])
    ?>
</section>
<section class="content">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4>
                <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
            </h4>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="background-color: #f1f0ee;">
            <li class="active"><a data-toggle="tab" href="#tabCatalog"><h5
                        class="box-title"><?= Yii::t('message', 'frontend.views.vendor.edit_four', ['ru' => 'Редактирование']) ?></h5>
                </a></li>
            <li><a data-toggle="tab" href="#tabClients"><h5
                        class="box-title"><?= Yii::t('message', 'frontend.views.vendor.set_for_rest', ['ru' => 'Назначить ресторану']) ?></h5>
                </a></li>
        </ul>
        <div class="tab-content">
            <div id="tabCatalog" class="tab-pane fade in active">
                <div class="panel-body">
                    <div class="col-sm-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <?= Html::input('text', 'search', $searchString, ['class' => 'form-control pull-left', 'placeholder' => Yii::t('message', 'frontend.views.vendor.search_five', ['ru' => 'Поиск']), 'id' => 'search']) ?>
                        </div>
                    </div>
                    <?=
                    Modal::widget([
                        'id'            => 'add-product',
                        'clientOptions' => ['style' => 'margin-top:13.2px;'],
                        'toggleButton'  => [
                            'label'       => '<i class="fa fa-plus-circle"></i> ' . Yii::t('message', 'frontend.views.vendor.new_good', ['ru' => 'Новый товар']),
                            'tag'         => 'a',
                            'data-target' => '#add-product-market-place',
                            'class'       => 'btn btn-fk-success btn-sm pull-right',
                            'href'        => Url::to(['/vendor/ajax-create-product-market-place', 'id' => $cat_id]),
                        ],
                    ])
                    ?>
                    <?= ''
                    ?>
                    <div class="btn-group pull-right" placement="left" style="margin-right: 10px">
                        <div class="btn-group" role="group">
                            <div class="btn-group">
                                <button id="delete_restore" class="btn btn-outline-default btn-sm pull-right btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="glyphicon glyphicon-export"></i> <span class="text-label"><?= $buttonDeleteRestore ?></span> <span class="caret"></span>
                                </button>
                                <ul id="delete_menu" class="dropdown-menu">
                                    <li><a id="delete_item" class="delete-all-products" href="#" tabindex="-1">
                                            <?= $buttonDelete ?>
                                        </a></li>
                                    <li><a id="restore_item" class="restore-catalog" href="#" tabindex="-1">
                                            <?= $buttonRestore ?>
                                        </a></li>
                                </ul>
                            </div>
                        </div>                    
                    </div>
                    <div class="btn-group pull-right" placement="left" style="margin-right: 10px">
                        <?=
                        ExportMenu::widget([
                            'dataProvider'       => $dataProvider,
                            'columns'            => $exportColumns,
                            'fontAwesome'        => true,
                            'filename'           => Yii::t('message', 'frontend.views.vendor.main_catalog_six', ['ru' => 'Главный каталог - ']) . date('Y-m-d'),
                            'encoding'           => 'UTF-8',
                            'target'             => ExportMenu::TARGET_SELF,
                            'showConfirmAlert'   => false,
                            'showColumnSelector' => false,
                            'batchSize'          => 200,
                            'timeout'            => 0,
                            'dropdownOptions'    => [
                                'label' => '<span class="text-label">' . Yii::t('message', 'frontend.views.vendor.export', ['ru' => 'экспорт']) . ' </span>',
                                'class' => ['btn btn-outline-default btn-sm pull-right'],
                                'title' => Yii::t('message', 'frontend.views.vendor.export', ['ru' => 'экспорт']),
                            ],
                            'exportConfig'       => [
                                ExportMenu::FORMAT_HTML    => false,
                                ExportMenu::FORMAT_TEXT    => false,
                                ExportMenu::FORMAT_EXCEL   => false,
                                ExportMenu::FORMAT_PDF     => false,
                                ExportMenu::FORMAT_CSV     => false,
                                ExportMenu::FORMAT_EXCEL_X => [
                                    'label'        => Yii::t('kvexport', 'Excel'),
                                    'icon'         => 'file-excel-o',
                                    'iconOptions'  => ['class' => 'text-success'],
                                    'linkOptions'  => [],
                                    'options'      => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                                    'alertMsg'     => Yii::t('kvexport', 'Файл EXCEL( XLSX ) будет генерироваться для загрузки'),
                                    'mime'         => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'extension'    => 'xlsx',
                                    //'writer' => 'Excel2007',
                                    'styleOptions' => [
                                        'font' => [
                                            'bold'  => true,
                                            'color' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                        ],
                                        'fill' => [
                                            'type'       => PHPExcel_Style_Fill::FILL_NONE,
                                            'startcolor' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                            'endcolor'   => [
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
                        'id'            => 'importToXls',
                        'clientOptions' => false,
                        'size'          => 'modal-md',
                        'toggleButton'  => [
                            'label'       => '<i class="glyphicon glyphicon-import"></i> <span class="text-label">' . Yii::t('message', 'frontend.views.vendor.import_five', ['ru' => 'импорт']) . ' </span>',
                            'tag'         => 'a',
                            'data-target' => '#importToXls',
                            'class'       => 'btn btn-outline-default btn-sm pull-right',
                            'href'        => Url::to(['/vendor/import', 'id' => $cat_id]),
                            'style'       => 'margin-right:10px;',
                        ],
                    ])
                    ?>
                    <?=
                    Html::button('<span class="text-label">' . Yii::t('message', 'frontend.views.vendor.change_index', ['ru' => 'Изменить индекс:']) . ' </span> <span class="base_index">' . common\models\Catalog::getMainIndexesList()["$currentCatalog->main_index"] . '</span>', [
                        'class' => 'btn btn-outline-default btn-sm pull-right',
                        'style' => ['margin-right' => '10px;'],
                        'id'    => 'changeBaseIndex',
                    ])
                    ?>
                    <?=
                    Html::button('<span class="text-label">' . Yii::t('message', 'frontend.views.vendor.change_curr', ['ru' => 'Изменить валюту:']) . ' </span> <span class="currency-symbol">' . $currentCatalog->currency->symbol . '</span>', [
                        'class' => 'btn btn-outline-default btn-sm pull-right',
                        'style' => ['margin-right' => '10px;'],
                        'id'    => 'changeCurrency',
                    ])
                    ?>

                </div>
                <div class="panel-body">
                    <?php
                    $gridColumnsBaseCatalog = [
                        [
                            'attribute'      => 'article',
                            'label'          => Yii::t('message', 'frontend.views.vendor.art_six', ['ru' => 'Артикул']),
                            'value'          => 'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'product',
                            'label'     => Yii::t('message', 'frontend.views.vendor.name_of_good_two', ['ru' => 'Наименование']),
                            'format'    => 'raw',
                            'value'     => function ($data) {
                                return Html::decode(Html::decode($data['product']));
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;width:20%'],
                        ],
                        [
                            'attribute' => 'units',
                            'label'     => Yii::t('message', 'frontend.views.vendor.multiplicity_six', ['ru' => 'Кратность']),
                            'value'     => function ($data) {
                                return empty($data['units']) ? '' : $data['units'];
                            },
                            'contentOptions'        => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'category_id',
                            'label'     => Yii::t('message', 'frontend.views.vendor.category_two', ['ru' => 'Категория']),
                            'value'     => function ($data) {
                                $data['category_id'] == 0 ? $category_name = '' : $category_name = Yii::t('app', \common\models\MpCategory::find()->where(['id' => $data['category_id']])->one()->name);
                                return $category_name;
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute'      => 'price',
                            'label'          => Yii::t('message', 'frontend.views.vendor.price_five', ['ru' => 'Цена']) . ' ' . $currentCatalog->currency->iso_code,
                            'value'          => 'price',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'ed',
                            'label'     => Yii::t('message', 'frontend.views.vendor.measure_three', ['ru' => 'Ед. измерения']),
                            'value'     => function ($data) {
                                return Yii::t('app', $data['ed']);
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                        ],
                        [
                            'attribute' => 'status',
                            'label'     => Yii::t('message', 'frontend.views.vendor.in_stock_three', ['ru' => 'Наличие']),
                            'format'    => 'raw',
                            'value'     => function ($data) {
                                $link = CheckboxX::widget([
                                            'name'          => 'status_' . $data['id'],
                                            'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                            'value'         => $data['status'],
                                            'autoLabel'     => true,
                                            'options'       => ['id' => 'status_' . $data['id'], 'data-id' => $data['id'], 'event-type' => 'set-status', 'value' => $data['status']],
                                            'pluginOptions' => [
                                                'threeState'    => false,
                                                'theme'         => 'krajee-flatblue',
                                                'enclosedLabel' => true,
                                                'size'          => 'lg',
                                            ]
                                ]);
                                return $link;
                            },
                        ],
                        [
                            'attribute'      => 'market_place',
                            'label'          => 'MixMarket',
                            'format'         => 'raw',
                            'contentOptions' => ['style' => 'width:80px'],
                            'headerOptions'  => ['class' => 'text-center'],
                            'value'          => function ($data) {
                                $data['market_place'] == 0 ?
                                        $res = '' :
                                        $res = '<center><i style="font-size: 28px;color:#84bf76;" class="fa fa-check-square-o"></i></center>';
                                return $res;
                            },
                        ],
                        [
                            'attribute'      => '',
                            'label'          => '',
                            'format'         => 'raw',
                            'contentOptions' => ['style' => 'width:80px'],
                            'headerOptions'  => ['class' => 'text-center'],
                            'value'          => function ($data) {
                                $data['market_place'] == 0 ?
                                        $link = Html::a(Yii::t('message', 'frontend.views.vendor.change_two', ['ru' => 'ИЗМЕНИТЬ']), ['/vendor/ajax-update-product-market-place',
                                            'id' => $data['id']], [
                                            'data'  => [
                                                'target'   => '#add-product-market-place',
                                                'toggle'   => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'class' => 'btn btn-sm btn-outline-success'
                                        ]) :
                                        $link = Html::a(Yii::t('message', 'frontend.views.vendor.change_three', ['ru' => 'ИЗМЕНИТЬ']), ['/vendor/ajax-update-product-market-place',
                                            'id' => $data['id']], [
                                            'data'  => [
                                                'target'   => '#add-product-market-place',
                                                'toggle'   => 'modal',
                                                'backdrop' => 'static',
                                            ],
                                            'class' => 'btn btn-sm btn-success'
                                ]);
                                return $link;
                            },
                        ],
                        [
                            'attribute'      => '',
                            'label'          => '',
                            'format'         => 'raw',
                            'contentOptions' => ['style' => 'width:50px;'],
                            'value'          => function ($data) {
                                $link = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                                            'class' => 'btn btn-sm btn-danger del-product',
                                            'data'  => ['id' => $data['id']],
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
                                'dataProvider'     => $dataProvider,
                                'pjax'             => true, // pjax is set to always true for this demo
                                'pjaxSettings'     => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                'filterPosition'   => false,
                                'columns'          => $gridColumnsBaseCatalog,
                                /* 'rowOptions' => function ($data, $key, $index, $grid) {
                                  return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                  }, */
                                'options'          => ['class' => 'table-responsive'],
                                'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                'bordered'         => false,
                                'striped'          => true,
                                'condensed'        => false,
                                'responsive'       => false,
                                'hover'            => true,
                                'resizableColumns' => false,
                                'export'           => [
                                    'fontAwesome' => true,
                                ],
                                'summary'          => Yii::t('message', 'frontend.views.request.showed_three') . " {begin} - {end} " . Yii::t('app', 'из') . " {totalCount} " . Yii::t('app', 'записей'),
                                'pager'            => [
                                    'firstPageLabel' => false,
                                    'lastPageLabel'  => false,
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
                        'label' => Yii::t('message', 'frontend.views.vendor.rest_three', ['ru' => 'Ресторан']),
                        'value' => function ($data) {
                            $organization_name = common\models\Organization::find()->where(['id' => $data->rest_org_id])->one()->name;
                            return $organization_name;
                        }
                    ],
                    [
                        'label'  => Yii::t('message', 'frontend.views.vendor.curr_cat', ['ru' => 'Текущий каталог']),
                        'format' => 'raw',
                        'value'  => function ($data) {
                            $catalog      = common\models\Catalog::find()->where(['id' => $data->cat_id])->one();
                            $catalog_name = ($data->cat_id == 0 || !$catalog) ? '' : $catalog->name;
                            return Yii::t('app', $catalog_name);
                        }
                    ],
                    [
                        'attribute'      => Yii::t('message', 'frontend.views.vendor.set_two', ['ru' => 'Назначить']),
                        'format'         => 'raw',
                        'contentOptions' => ['style' => 'width:50px;'],
                        'value'          => function ($data) {
                            $value = $data->status && ($data->cat_id == Yii::$app->request->get('id'));
                            $link  = CheckboxX::widget([
                                        'name'          => 'setcatalog_' . $data->id,
                                        'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                        'value'         => $value,
                                        'autoLabel'     => true,
                                        'options'       => ['id' => 'setcatalog_' . $data->id, 'data-id' => $data->rest_org_id, 'event-type' => 'set-catalog', 'value' => $value],
                                        'pluginOptions' => [
                                            'threeState'    => false,
                                            'theme'         => 'krajee-flatblue',
                                            'enclosedLabel' => true,
                                            'size'          => 'lg',
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
                            'dataProvider'   => $dataProvider2,
                            'filterModel'    => $searchModel2,
                            'filterPosition' => false,
                            'columns'        => $gridColumnsCatalog,
                            'options'        => ['class' => 'table-responsive'],
                            'tableOptions'   => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter'      => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'summary'        => false,
                            'bordered'       => false,
                            'striped'        => true,
                            'condensed'      => false,
                            'responsive'     => false,
                            'hover'          => false,
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
$baseCatalogUrl             = Url::to(['vendor/basecatalog', 'id' => $currentCatalog->id]);
$changeCatalogPropUrl       = Url::to(['vendor/changecatalogprop']);
$changeSetCatalogUrl        = Url::to(['vendor/changesetcatalog']);
$deleteProductUrl           = Url::to(['vendor/ajax-delete-product']);
$deleteAllUrl               = Url::to(["/vendor/ajax-delete-main-catalog"]);
$restoreCatalogLastSnapshot = Url::to(["/vendor/ajax-restore-main-catalog-last-snapshot"]);
$changeMainIndexUrl         = Url::to(["/vendor/ajax-change-main-index"]);
$restoreCatalogUrl          = Url::to(["/vendor/ajax-restore-main-catalog-latest-snapshot"]);

$var1             = Yii::t('message', 'frontend.views.vendor.del_good', ['ru' => 'Удалить этот продукт?']);
$var2             = Yii::t('message', 'frontend.views.vendor.will_remove', ['ru' => 'Продукт будет удален из всех каталогов']);
$var3             = Yii::t('message', 'frontend.views.vendor.del_four', ['ru' => 'Удалить']);
$var4             = Yii::t('message', 'frontend.views.vendor.cancel_eleven', ['ru' => 'Отмена']);
$var5             = Yii::t('message', 'frontend.views.vendor.wrong', ['ru' => 'Что-то пошло не так']);
$var6             = Yii::t('message', 'frontend.views.vendor.change_curr_two', ['ru' => 'Изменение валюты каталога']);
$var7             = Yii::t('message', 'frontend.views.vendor.choose_curr', ['ru' => 'Выберите новую валюту каталога']);
$var8             = Yii::t('message', 'frontend.views.vendor.choose_from_list', ['ru' => 'Выберите валюту из списка']);
$var9             = Yii::t('message', 'frontend.views.vendor.in_use', ['ru' => 'Данная валюта уже используется!']);
$var10            = Yii::t('message', 'frontend.views.vendor.curr_changed', ['ru' => 'Валюта каталога изменена!']);
$var11            = Yii::t('message', 'frontend.views.vendor.set_prices', ['ru' => 'Пересчитать цены в каталоге?']);
$var12            = Yii::t('message', 'frontend.views.vendor.prices_changed', ['ru' => 'Цены успешно изменены!']);
$var13            = Yii::t('message', 'frontend.views.vendor.farther', ['ru' => 'Далее']);
$titleChangeIndex = Yii::t('message', 'frontend.views.vendor.change_index', ['ru' => 'Изменить индекс:']);
$indexReject      = Yii::t('message', 'frontend.views.vendor.index_reject', ['ru' => 'Этот индекс уже используется']);

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
$(document).on("click", ".del-product", function(e){
    var id = $(this).attr('data-id');
        
	bootbox.confirm({
            title: "$var1",
            message: "$var2", 
            buttons: {
                confirm: {
                    label: '$var3',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$var4',
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
                            if(response.success) {
                                $.pjax.reload({container: "#kv-unique-id-1"}); 
                            } else {
                                console.log('$var5');    
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
$(document).on("hidden.bs.modal", "#importToXls", function() {
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
    
    // var currencies = $.map($currencySymbolList, function(el) { return el });
    var currencies = $currencySymbolList;
    
    console.log(currencies);
    var newCurrency = {$currentCatalog->currency->id};
    var currentCurrency = {$currentCatalog->currency->id};
    var oldCurrency = {$currentCatalog->currency->id};
        
    $(document).on("click", "#changeCurrency", function() {
        swal({
            title: '$var6',
            input: 'select',
            inputOptions: $currencyList,
            inputPlaceholder: '$var7',
            showCancelButton: true,
            cancelButtonText: '$var4',
            showLoaderOnConfirm: true,
            confirmButtonText: '$var13',
            cancelButtonText: '$cancelText',
            allowOutsideClick: false,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (!value) {
                        reject('$var8')
                    }
                    if (value != currentCurrency) {
                        newCurrency = value;
                        resolve();
                    } else {
                        reject('$var9')
                    }
                })
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    resolve();
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    title: '',
                    html: 
                        '<div>$var11</div>' +
                        '<input id="swal-curr1" class="swal2-input" style="width: 50px;display:inline;" value=1> ' + currencies[oldCurrency] + ' = ' +
                        '<input id="swal-curr2" class="swal2-input" style="width: 50px;display:inline;" value=1> ' + currencies[newCurrency],
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    cancelButtonText: '$cancelText',
                    allowOutsideClick: false,
                    preConfirm: function () {
                        return new Promise(function (resolve) {
                            $.post(
                                "{$changeCurrencyUrl}",
                                {newCurrencyId: newCurrency}
                            ).done(function (response) {
                                if (response.result === 'success') {
                                    $(".currency-symbol").html(response.symbol);
                                    $(".currency-iso").html(response.iso_code);
                                    oldCurrency = currentCurrency;
                                    currentCurrency = newCurrency;
                                } else {
                                    swal({
                                        type: response.result,
                                        title: response.message
                                    });
                                }
                            });
                            
                            $.post(
                                '{$calculatePricesUrl}',
                                {oldCurrencyUnits: $('#swal-curr1').val(), newCurrencyUnits: $('#swal-curr2').val()}
                            ).done(function (response) {
                                if (response.result === 'success') {
                                    $.pjax.reload({container: "body", timeout:1000000});
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
                    if (result.dismiss === "cancel") {
                        swal.close();
                    } else {
                        swal({
                            type: "success",
                            title: "$var12",
                            allowOutsideClick: true,
                        });
                    }
                })
            }
        })        
    });

    var indexes = $indexesList;
    
    var newIndex = '{$currentCatalog->main_index}';
    var currentIndex = '{$currentCatalog->main_index}';
        
    $(document).on("click", "#changeBaseIndex", function() {
        swal({
            title: '$titleChangeIndex',
            input: 'select',
            inputOptions: $indexesList,
            showCancelButton: true,
            showLoaderOnConfirm: true,
            confirmButtonText: '$var13',
            cancelButtonText: '$cancelText',
            allowOutsideClick: false,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (value != currentIndex) {
                        newIndex = value;
                        resolve();
                    } else {
                        swal({
                            type: "error",
                            title: "$indexReject"
                        });
                    }
                })
            },
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "{$changeMainIndexUrl}",
                        {
                            "cat_id": {$cat_id},
                            "main_index": newIndex
                        }
                    ).done(function (response) {
                        if (response) {
                            resolve();
                        } else {
                            swal({
                                type: "error",
                                title: "$catalogNotEmpty"
                            });
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    type: "success",
                    title: "$indexChanged",
                    allowOutsideClick: true,
                }).then(function (result) {
                    $.pjax.reload({container: "body", timeout:1000000});
                });
            }
        })        
    });
   
    $(document).on("click", ".delete-all-products", function(e) {
        e.preventDefault();                            
        swal({
            title: '$titleDeleteAll',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            confirmButtonText: '$var13',
            cancelButtonText: '$cancelText',
            allowOutsideClick: false,
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "{$deleteAllUrl}"
                    ).done(function (response) {
                        if (response) {
                            $.pjax.reload({container: "#kv-unique-id-1"}); 
                            resolve();
                        } else {
                            swal({
                                type: "error",
                                title: "$catalogDeletionFailed"
                            });
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    type: "success",
                    title: "$catalogDeleted",
                    allowOutsideClick: true,
                });
            }
        })        
    });
   
    $(document).on("click", ".restore-catalog", function(e) {
        e.preventDefault();                            
        swal({
            title: '$titleRestoreCatalog',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            confirmButtonText: '$var13',
            cancelButtonText: '$cancelText',
            allowOutsideClick: false,
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        "{$restoreCatalogUrl}"
                    ).done(function (response) {
                        if (response) {
                            $.pjax.reload({container: "#kv-unique-id-1"}); 
                            resolve();
                        } else {
                            swal({
                                type: "error",
                                title: "$catalogRestorationFailed"
                            });
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    type: "success",
                    title: "$catalogRestored",
                    allowOutsideClick: true,
                });
            }
        })        
    });
   
    $(document).on("change", ".decimal_number", function(e) {
        value = $(this).val();
        $(this).val(value.replace(",", "."));
    });
    $.pjax.defaults.maxCacheLength = 0;    
JS;
$this->registerJs($customJs, View::POS_READY);
?>
