<?php
use kartik\grid\GridView;
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
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
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
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Главный Каталог</h3>
        <span class="pull-right"><?=Html::a('<i class="fa fa-fw fa-chevron-left"></i>  Вернуться к списку каталогов',['vendor/catalogs'])?></span>
    
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tabCatalog">Редактирование</a></li>
                <li><a data-toggle="tab" href="#tabClients">Назначить ресторану</a></li>
            </ul>
        </div>
    <div class="tab-content">
        <div id="tabCatalog" class="tab-pane fade in active">
                <div class="panel-body">
    <div class="col-sm-4">
        <?=Html::input('text', 'search', $searchString, ['class' => 'form-control pull-left','placeholder'=>'Поиск','id'=>'search']) ?>
    </div>   
                <?=
                Modal::widget([
                    'id' => 'add-product',
                    'clientOptions' => false,
                    'toggleButton' => [
                        'label' => '<i class="fa fa-plus"></i> Новый продукт',
                        'tag' => 'a',
                        'data-target' => '#add-product',
                        'class' => 'btn btn-fk-success btn-sm pull-right',
                        'href' => Url::to(['/vendor/ajax-create-product','id' => Yii::$app->request->get('id')]),
                    ],
                ])
                ?><div class="btn-group pull-right" placement="left" style="margin-right: 10px">
                    <?= ExportMenu::widget([
                                'dataProvider' => $dataProvider,
                                'columns' => $exportColumns,
                                'fontAwesome' => true,
                                'filename'=>'Главный каталог - '.date('Y-m-d'),
                                'encoding'=>'UTF-8',
                                'target' => ExportMenu::TARGET_SELF,
                                'showConfirmAlert'=>false,
                                'showColumnSelector'=>false,
                                'dropdownOptions' => [
                                    'label' => 'Скачать каталог',
                                    'class' => ['btn btn-default btn-sm pull-right']
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
                
                <?=
                    Modal::widget([
                        'id' => 'importToXls',
                        'clientOptions' => false,
                        'size'=>'modal-md',
                        'toggleButton' => [
                            'label' => '<i class="glyphicon glyphicon-import"></i> Импорт',
                            'tag' => 'a',
                            'data-target' => '#importToXls',
                            'class' => 'btn btn-default btn-sm pull-right',
                            'href' => Url::to(['/vendor/import-to-xls','id' => Yii::$app->request->get('id')]),
                            'style' => 'margin-right:10px;',
                        ],
                    ])
                ?>
                <?= Html::a(
                   '<i class="fa fa-list-alt"></i> Скачать шаблон',
                   Url::to('@web/upload/template.xlsx'),
                   ['class' => 'btn btn-default btn-sm pull-right','style' => ['margin-right'=>'10px;']]
               ) ?>   
               <?=
                   Modal::widget([
                       'id' => 'info',
                       'clientOptions' => false,
                       'size'=>'modal-md',
                       'toggleButton' => [
                           'label' => '<i class="fa fa-question-circle" aria-hidden="true"></i> Инструкция',
                           'tag' => 'a',
                           'data-target' => '#info',
                           'class' => 'btn btn-default btn-sm pull-right',
                           'href' => Url::to(['#']),
                           'style' => 'margin-right:10px;',
                       ],
                   ])
               ?>
            </div>
            <?php 
         /*   $gridColumnsBaseCatalog = [
                            [
                            'attribute' => 'article',
                            'label'=>'Артикул',
                            'value'=>'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'product',
                            'label'=>'Наименование',
                            'value'=>'product',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'units',
                            'label'=>'Кратность',
                            'value'=>'units',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],    
                            ],
                            [
                            'attribute' => 'category_id',
                            'label'=>'Категория',
                            'value'=>function ($data) {
                            $data['category_id']==0 ? $category_name='':$category_name=Category::get_value($data['category_id'])->name;
                            return $category_name;
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'price',
                            'label'=>'Цена',
                            'value'=>'price',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'status',
                            'label'=>'Наличие',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:100px;'],
                            'value' => function ($data) {
                                $link = CheckboxX::widget([
                                    'name'=>'status_'.$data['id'],
                                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'value'=>$data['status']==0 ? 0 : 1,
                                    'autoLabel' => false,
                                    'options'=>['id'=>'status_'.$data['id'], 'data-id'=>$data['id']],
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
                        'label' => '',
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'width:50px;'],
                        'value' => function ($data) {
                            $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['/vendor/ajax-update-product', 'id' => $data['id']], [
                                'data' => [
                                'target' => '#add-product',
                                'toggle' => 'modal',
                                'backdrop' => 'static',
                                          ],
                                'class'=>'btn btn-sm btn-warning'

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
                            $link = Html::button('<i class="fa fa-trash m-r-xs"></i>',[
                                'class'=>'btn btn-sm btn-danger del-product',
                                'data'=>['id'=>$data['id']],
                            ]);
                            return $link;
                        },

                    ],
            ];
            ?>
           
            
                
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">               
                    <?php Pjax::begin(['enablePushState' => false, 'id' => 'products-list',]); ?>
                    <?=GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'columns' => $gridColumnsBaseCatalog, 
                            'tableOptions' => ['class' => 'table no-margin'],
                            'options' => ['class' => 'table-responsive'],
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => false,
                            'export' => [
                                'fontAwesome' => true,
                            ],
                    ]);
                    ?>  
                    <?php Pjax::end(); ?>
                    </div>
                </div> */
            ?>
                <div class="panel-body">
                    <?php Pjax::begin(['enablePushState' => false, 'id' => 'products-list',]); ?>
                        
                      <?php
                        $gridColumnsBaseCatalog = [
                            [
                            'attribute' => 'article',
                            'label'=>'Артикул',
                            'value'=>'article',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'product',
                            'label'=>'Наименование',
                            'value'=>'product',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'units',
                            'label'=>'Кратность',
                            'value'=>'units',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:120px;'],    
                            ],
                            [
                            'attribute' => 'category_id',
                            'label'=>'Категория',
                            'value'=>function ($data) {
                            $data['category_id']==0 ? $category_name='':$category_name=Category::get_value($data['category_id'])->name;
                            return $category_name;
                            },
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'price',
                            'label'=>'Цена',
                            'value'=>'price',
                            'contentOptions' => ['style' => 'vertical-align:middle;'],
                            ],
                            [
                            'attribute' => 'status',
                            'label'=>'Наличие',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'vertical-align:middle;width:100px;'],
                            'value' => function ($data) {
                                $link = CheckboxX::widget([
                                    'name'=>'status_'.$data['id'],
                                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'value'=>$data['status']==0 ? 0 : 1,
                                    'autoLabel' => false,
                                    'options'=>['id'=>'status_'.$data['id'], 'data-id'=>$data['id']],
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
                                'label' => '',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width:50px;'],
                                'value' => function ($data) {
                                    $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['/vendor/ajax-update-product', 'id' => $data['id']], [
                                        'data' => [
                                        'target' => '#add-product',
                                        'toggle' => 'modal',
                                        'backdrop' => 'static',
                                                  ],
                                        'class'=>'btn btn-sm btn-warning'

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
                                    $link = Html::button('<i class="fa fa-trash m-r-xs"></i>',[
                                        'class'=>'btn btn-sm btn-danger del-product',
                                        'data'=>['id'=>$data['id']],
                                    ]);
                                    return $link;
                                },

                            ],
                        ];
                        ?>    

                        <div class="panel-body">
                            <div class="box-body table-responsive no-padding">
                            <?=GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterPosition' => false,
                                'columns' => $gridColumnsBaseCatalog, 
                                'tableOptions' => ['class' => 'table no-margin'],
                                'options' => ['class' => 'table-responsive'],
                                'bordered' => false,
                                'striped' => true,
                                'condensed' => false,
                                'responsive' => false,
                                'hover' => false,
                                'export' => [
                                    'fontAwesome' => true,
                                ],
                            ]);
                            ?> 
                            </div>
                        </div>      
                        
                    <?php Pjax::end(); ?>
                </div>       
                     
            </div>
            <div id="tabClients" class="tab-pane fade"> 	    
                <?php 
                $gridColumnsCatalog = [
                    [
                    'label'=>'Ресторан',
                    'value'=>function ($data) {
                    $organization_name=common\models\Organization::find()->where(['id'=>$data->rest_org_id])->one()->name;
                    return $organization_name;
                    }
                    ],
                    [
                    'label'=>'Текущий каталог',
                    'format' => 'raw',
                    'value'=>function ($data) {
                    $catalog_name = $data->cat_id == 0 ? '' : 
                    common\models\Catalog::find()->where(['id'=>$data->cat_id])->one()->name;
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
                            'value'=>$data->cat_id == Yii::$app->request->get('id') ? 1 : 0,
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
                    <?php Pjax::begin(['enablePushState' => false, 'id' => 'clients-list',]); ?>
                    <?=GridView::widget([
                        'dataProvider' => $dataProvider2,
                        'filterModel' => $searchModel2,
                        'filterPosition' => false,
                        'columns' => $gridColumnsCatalog, 
                        'tableOptions' => ['class' => 'table no-margin'],
                        'options' => ['class' => 'table-responsive'],
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
<?php
$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        url: 'index.php?r=vendor/basecatalog&id=$currentCatalog',
        container: '#products-list',
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
$('.cbx-container').live('click', function(e) {
    //
    var id = $(this).children('input[type=checkbox]').attr('data-id');
    var state = $(this).children('input[type=checkbox]').prop("checked");
    var elem = $(this).children('input[type=checkbox]').attr('name').substr(0, 6);
    if(elem=="status"){statusOrMarket(elem,id,state);$(this).children('input[type=checkbox]').change();}
    //if(elem=="market"){statusOrMarket(elem,id,state);}
    if(elem=="setcat"){setRestOrgCatalog(id,state);}   
	function statusOrMarket(elem,id,state){
		$.ajax({
	        url: "index.php?r=vendor/changecatalogprop",
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
	}
	function setRestOrgCatalog(id,state){
		$.ajax({
	        url: "index.php?r=vendor/changesetcatalog",
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
	}
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
	        url: "index.php?r=vendor/ajax-delete-product",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
                        //$.pjax.reload({container: "#clients-list"});
			        $.pjax.reload({container: "#products-list"}); 
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>