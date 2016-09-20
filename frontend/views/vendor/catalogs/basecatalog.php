<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
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
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
?>
<?php 
$this->title = 'Основной каталог';
?>
<?=Modal::widget([
	'id' => 'add-edit-product',
	'clientOptions' => false,
	])
?>
<?php
$exportFilename = 'catalog_' . date("Y-m-d_H-m-s");
$exportColumns = [
		[
		'label'=>'Артикул',
		'value'=>'article',
		],
		[
		'label'=>'Продукт',
		'value'=>'product',
		],
		[
		'label'=>'кол-во',
		'value'=>'units',
		],
		[
		'label'=>'Цена',
		'value'=>'price',
		]
    ]
?>           

<div class="panel-body">
    <?= Html::a(
        '<i class="fa fa-reply m-r-xs"></i> Назад',
        ['vendor/catalogs'],
        ['class' => 'btn btn-lg btn-default pull-right step-1','style' => 'margin-left:10px;']
    ) 
    ?>
    <h3 class="font-light"><i class="fa fa-list-alt"></i> Главный Каталог</h3>
</div>
<div class="panel-body">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tabCatalog">Редактирование</a></li>
        <li><a data-toggle="tab" href="#tabClients">Назначить</a></li>
    </ul>
</div>
    <div class="tab-content">
        <div id="tabCatalog" class="tab-pane fade in active">
            <div class="hpanel">
                <div class="panel-body">
                <?=
                Modal::widget([
                    'id' => 'add-product',
                    'clientOptions' => false,
                    'toggleButton' => [
                        'label' => '<i class="fa fa-plus"></i> Новый продукт',
                        'tag' => 'a',
                        'data-target' => '#add-product',
                        'class' => 'btn btn-info m-t-xs m-r pull-right',
                        'href' => Url::to(['/vendor/ajax-create-product','id' => Yii::$app->request->get('id')]),
                    ],
                ])
                ?><div class="btn-group m-t-xs m-r pull-right" placement="left" style="margin-right: 10px">
                    <?= ExportMenu::widget([
                                'dataProvider' => $dataProvider,
                                'columns' => $exportColumns,
                                'fontAwesome' => true,
                                'filename'=>'Catalog'.date('Y-m-d H:i:s'),
                                'encoding'=>'UTF-8',
                                'target' => ExportMenu::TARGET_SELF,
                                'showConfirmAlert'=>false,
                                'columnSelectorOptions'=>[
                                    'label' => '',
                                    'class' => 'btn btn-default '
                                    ],
                                'dropdownOptions' => [
                                    'label' => 'Скачать каталог',
                                    'class' => 'btn btn-default'
                                    ],
                                'exportConfig' => [
                                    ExportMenu::FORMAT_HTML => false,
                                    ExportMenu::FORMAT_TEXT => false,
                                    ExportMenu::FORMAT_EXCEL => false,
                                    ExportMenu::FORMAT_PDF => false,
                                    ExportMenu::FORMAT_CSV => [
                                        'label' => Yii::t('kvexport', 'CSV'),
                                        'icon' => 'file-code-o',
                                        'iconOptions' => ['class' => 'text-primary'],
                                        'linkOptions' => [],
                                        'options' => ['title' => Yii::t('kvexport', 'Comma Separated Values')],
                                        'alertMsg' => Yii::t('kvexport', 'Вы загружаете CSV файл.'),
                                        'mime' => 'application/csv;charset=UTF-8',
                                        'extension' => 'csv',
                                        'writer' => 'CSV'
                                    ],
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
                                        'styleOptions'=>[
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
                    <div class="btn-group m-t-xs m-r pull-right" placement="left" style="margin-right: 10px">
                        <?=
                            Modal::widget([
                                'id' => 'importToXls',
                                'clientOptions' => false,
                                'size'=>'modal-md',
                                'toggleButton' => [
                                    'label' => '<i class="glyphicon glyphicon-import"></i> Импорт',
                                    'tag' => 'a',
                                    'data-target' => '#importToXls',
                                    'class' => 'btn btn-default',
                                    'href' => Url::to(['/vendor/import-to-xls','id' => Yii::$app->request->get('id')]),
                                    'style' => '',
                                ],
                            ])
                        ?>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu m-t-sm">
                            <li>
                                <a href="upload/template.xlsx" class="ng-binding">
                                    <i class="fa fa-list-alt m-r-xs"></i> Скачать шаблон
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button style="margin-right: 10px; margin-left: 10px;" class="btn btn-default m-t-xs m-r pull-right ng-binding" ng-click="tour.restart(true)"><i class="fa fa-question-circle"></i> Инструкция</button>
                </div>
            </div>
            
<?php 
$gridColumnsBaseCatalog = [
                    [
                'label' => '',  
                'format' => 'raw',  
                'contentOptions' => ['style' => 'width:50px;'],
                'value' => function ($data) {
                     $data->image?$imgUrl=$data->image:$imgUrl='NOIMAGE.gif';
                     $images = Html::img(\Yii::$app->request->BaseUrl.'/upload/'.$imgUrl,
                        [
                        'alt'=>'',
                        'width'=>'50',
                        'height'=>'50', 
                        'data-toggle'=>'tooltip',
                        'data-placement'=>'left',
                        'title' => '' ,
                        'style'=>'cursor:default;'
                        ]);
                    return ($images);
                    }
                ],
		[
		'label'=>'Артикул',
		'value'=>'article',
		],
		[
		'label'=>'Продукт',
		'value'=>'product',
		],
		[
		'label'=>'кол-во',
		'value'=>'units',
		],
                [
		'label'=>'Категория',
                'value'=>function ($data) {
                $data->category_id==0 ? $category_name='':$category_name=Category::get_value($data->category_id)->name;
                return $category_name;
                }
		],
		[
		'label'=>'Цена',
                'value'=>function ($data) {return $data->price;},
		],
        [
            'attribute' => 'статус',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                    'name'=>'status_'.$data->id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->status==0 ? 0 : 1,
                    'autoLabel' => true,
                    'options'=>['id'=>'status_'.$data->id, 'data-id'=>$data->id],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
            },
            
        ],
        [
            'attribute' => 'MarketPlace',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:100px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                    'name'=>'marketplace_'.$data->id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->market_place==0 ? 0 : 1,
                    'autoLabel' => true,
                    'options'=>['id'=>'marketplace_'.$data->id, 'data-id'=>$data->id],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
            },
            
        ],
        [
            'attribute' => '',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = Html::button('<i class="fa fa-trash m-r-xs"></i>',[
                    'class'=>'btn btn-danger del-product',
                    'data'=>['id'=>$data->id],
                ]);
                return $link;
            },
            
        ],
        [
            'attribute' => '',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['/vendor/ajax-update-product', 'id' => $data->id], [
                    'data' => [
                    'target' => '#add-product',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                              ],
                    'class'=>'btn btn-default'
                    
                ]);
                return $link;
            },
            
        ],
];
?>
            
<?php Pjax::begin(['enablePushState' => false, 'id' => 'products-list',]); ?>
<?php
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'search-form',
                'class' => "navbar-form",
                'role' => 'search',
            ],
            'method' => 'get',
        ]);
?>  
<?php ActiveForm::end(); ?>          

<div class="panel-body">   
<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsBaseCatalog, 
        'export' => [
            'fontAwesome' => true,
        ],
        'condensed'=>true,
        //'floatHeader'=>true, //зафиксировать заголовок
        'bordered'=>true,
]);
?>   
</div>
<?php Pjax::end(); ?>
        </div>
        <div id="tabClients" class="tab-pane fade">
<?php Pjax::begin(['enablePushState' => false, 'id' => 'clients-list',]); ?>
<?php
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'search-form2',
                'class' => "navbar-form",
                'role' => 'search',
            ],
            'method' => 'get',
        ]);
?>  
<?php ActiveForm::end(); ?> 	    
<?php 
$gridColumnsCatalog = [
        [
        'label'=>'Ресторан',
        'value'=>function ($data) {
        $organization_name=common\models\Organization::get_value($data->rest_org_id)->name;
        return $organization_name;
        }
        ],
        [
        'label'=>'Текущий каталог',
        'format' => 'raw',
        'value'=>function ($data) {
        $catalog_name = $data->cat_id==0 ? '' : 
        common\models\Catalog::get_value($data->cat_id)->name;
        return $catalog_name;
        }
        ],
        [
            'attribute' => 'Назначить',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                    'name'=>'setcatalog_'.$data->id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->status==1 && $data->cat_id ==Yii::$app->request->get('id') ? 1 : 0,
                    'autoLabel' => true,
                    'options'=>['id'=>'setcatalog_'.$data->id, 'data-id'=>$data->rest_org_id],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
            },
            
        ],
];
?>
        <div class="panel-body">
<?=GridView::widget([
	'dataProvider' => $dataProvider2,
	'filterModel' => $searchModel2,
	'filterPosition' => false,
	'columns' => $gridColumnsCatalog,
]);
?> 
        </div>
<?php Pjax::end(); ?>   
    </div>
</div>
<?php
$customJs = <<< JS
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
        
$('input[type=checkbox]').live('change', function(e) {
    var id = $(this).attr('data-id');
    var elem = $(this).attr('name').substr(0, 6);
    if(elem=="status"){statusOrMarket(elem,id);}
    if(elem=="market"){statusOrMarket(elem,id);}
    if(elem=="setcat"){setRestOrgCatalog(id);}   
	function statusOrMarket(elem,id){
		$.ajax({
	        url: "index.php?r=vendor/changecatalogprop",
	        type: "POST",
	        dataType: "json",
	        data: {'elem' : elem,'id' : id},
	        cache: false,
	        success: function(response) {
		        console.log(response)
		        //$.pjax.reload({container: "#products-list"});
		    },
		    failure: function(errMsg) {
	            console.log(errMsg);
	        }
		});
	}
	function setRestOrgCatalog(id){
		$.ajax({
	        url: "index.php?r=vendor/changesetcatalog",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id, 'curCat' : $currentCatalog},
	        cache: false,
	        success: function(response) {
		        console.log(response)
		        $.pjax.reload({container: "#clients-list"});
		    },
		    failure: function(errMsg) {
	            console.log(errMsg);
	        }
		});
	}
})
$("body").on("hidden.bs.modal", "#add-product", function() {
    $(this).data("bs.modal", null);
    $.pjax.reload({container: "#products-list"});
})

/*$("#importToXls").on("click", ".import", function(e) {
    e.preventDefault();
    var form = $("#import-form");
    var formData = new FormData($("#import-form")[0]);
    console.log(formData);
    var formData = form.serialize();    
    $.ajax({
	        url: form.attr("action"),
	        type: "POST",
	        dataType: "json",
	        data: formData,
	        cache: false,
	        success: function(response) {
        console.log(response);
        }
    });
});*/
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
	bootbox.confirm("<h3>Удалить этот продукт?</h3>", function(result) {
		if(result){
		$.ajax({
	        url: "index.php?r=vendor/ajax-delete-product",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
                        $.pjax.reload({container: "#clients-list"});
			        console.log(response); 
			        $.pjax.reload({container: "#products-list"}); 
			        }else{
				    console.log('Что-то пошло не так');    
			        }
		        }	
		    });
		}else{
		console.log('cancel');	
		}
	});      
})      
JS;
$this->registerJs($customJs, View::POS_READY);
?>