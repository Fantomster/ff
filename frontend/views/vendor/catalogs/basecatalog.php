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
?>
<?php	
$catalogBaseGoods = new CatalogBaseGoods();
	 

?>
<?php 
$this->title = 'Базовый каталог';
$this->params['breadcrumbs'][] = $this->title;	
?>
<div class="catalog-index">
<?=Modal::widget([
	'id' => 'add-edit-product',
	'clientOptions' => false,
	])
?>
<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tabCatalog">Каталог</a></li>
    <li><a data-toggle="tab" href="#tabClients">Мои рестораны</a></li>
</ul>
<div class="tab-content">
    <div id="tabCatalog" class="tab-pane fade in active">
<div class="row">
	<div class="col-lg-12">
		<div class="hpanel">
            <div class="panel-body">
	                    <!--button id="newProduct" data-target="#add-edit-product" data-toggle="modal" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Новый продукт</button-->
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
						        'style' => 'float:right',
						    ],
						])
						?>
						<?php
						//echo Yii::$app->getRequest()->getParam('id');
						?>
                <div class="btn-group m-t-xs m-r pull-right" placement="left" style="margin-right: 10px">
                    <button id="exportToXls" type="button" class="btn btn-primary"><i class="fa fa-download m-r-xs"></i> Выгрузить все продукты</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu m-t-sm">
                  	    <li>
                    	    <a id="importProduct">
                      	    	<i class="fa fa-upload m-r-xs"></i>
					  			Загрузить продукты
                    	    </a>
                  	    </li>
                            <li class="divider"></li>
                            <li><a href="upload/template.xlsx" class="ng-binding">
	                            <i class="fa fa-list-alt m-r-xs"></i> Скачать шаблон</a>
	                        </li>
                        </ul>
                </div>
                    <button style="margin-right: 10px; margin-left: 10px;" class="btn btn-default m-t-xs m-r pull-right ng-binding" ng-click="tour.restart(true)"><i class="fa fa-question-circle"></i> Инструкция</button>
                    <?= Html::a('<i class="fa fa-reply m-r-xs"></i> Назад', ['vendor/catalogs'],['class'=>'btn btn-default m-t-xs m-r pull-right']) ?>
                </div>
            </div>
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
<?/*=
$form->field($searchModel, 'product')->textInput([
    'id' => 'search-string',
    'class' => 'form-control',
    'placeholder' => 'Поиск'])->label(false)
*/?>
<?php ActiveForm::end(); ?>          
<?php 
$gridColumnsBaseCatalog = [
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
		],
		/*[
		    'attribute' => 'Категория',
		    'format' => 'raw',
		    'value' => function ($data) {
				$link = Html::dropDownList('s_ids', null,['0' => '','Список категорий' => Category::allCategory()]);
				return $link;
		    },
		],*/
		
		[
		'label'=>'Категория',
		'value'=>'category_id',
		],
        [
            'attribute' => 'Статус продукта',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = SwitchBox::widget([
					    'name' => 'status_'.$data->id,
					    'id'=>'status_'.$data->id,
					    'checked' => $data->status==0 ? false : true,
					    'clientOptions' => [
					        'onColor' => 'success',
					        'offColor' => 'default',
					        'onText'=>'Вкл',
					        'offText'=>'Выкл',
					        'baseClass'=>'bootstrap-switch',
					    ],
					    
					]);
                return $link;
            },
            
        ],
        [
            'attribute' => 'MarketPlace',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = SwitchBox::widget([
					    'name' => 'marketplace_'.$data->id,
					    'checked' => $data->market_place==0 ? false : true,
					    'clientOptions' => [
					        'onColor' => 'success',
					        'offColor' => 'default',
					        'onText'=>'Да',
					        'offText'=>'Нет',
					        'baseClass'=>'bootstrap-switch',
					    ],
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

<?=GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'filterPosition' => false,
	'columns' => $gridColumnsBaseCatalog,
	//'pjax'=>true,
    //'pjaxSettings'=>[
    //    'neverTimeout'=>true,
    //    'beforeGrid'=>'My fancy content before.',
    //    'afterGrid'=>'My fancy content after.',
    //],
	'export'=>[
        'fontAwesome'=>true
    ],   
]);
?>
<?php Pjax::end(); ?>
	</div>
</div>
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
<?/*=
$form->field($searchModel2, 'rest_org_id')->textInput([
    'id' => 'search-string2',
    'class' => 'form-control',
    'placeholder' => 'Поиск'])->label(false)
*/?>
<?php ActiveForm::end(); ?> 	    
<?php 
$gridColumnsCatalog = [
		[
		'label'=>'Ресторан',
		'value'=>function ($data) {
		return $data->rest_org_id;
		}
		//'rest_org_id',
		],
		[
		'label'=>'Текущий каталог',
		'value'=>function ($data) {
		$cat_id = $data->cat_id==0 ? 'Не назначен' : $data->cat_id;
		return $cat_id;
		}
		
		//'cat_id',
		],
        [
            'attribute' => 'Назначить каталог',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = SwitchBox::widget([
					    'name' => 'setcatalog_'.$data->rest_org_id,
					    'checked' => $data->status==1 && $data->cat_id ==Yii::$app->request->get('id') ? true : false,
					    'clientOptions' => [
					        'onColor' => 'success',
					        'offColor' => 'default',
					        'onText'=>'Да',
					        'offText'=>'Нет',
					        'baseClass'=>'bootstrap-switch',
					    ],
					]);
                return $link;
            },
            
        ],
];
?>	
<?=GridView::widget([
	'dataProvider' => $dataProvider2,
	'filterModel' => $searchModel2,
	'filterPosition' => false,
	'columns' => $gridColumnsCatalog,
]);
?> 
<?php Pjax::end(); ?>   
    </div>
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
$('input[type=checkbox]').live('switchChange.bootstrapSwitch', function (event, state) {	

var elem,e,id,state
elem = $(this).attr('name').substr(0, 6);
e = $(this).attr('name')
if(elem=="status"){id = e.replace('status_',''); statusOrMarket(elem,state,id);}
if(elem=="market"){id = e.replace('marketplace_',''); statusOrMarket(elem,state,id);}
if(elem=="setcat"){id = e.replace('setcatalog_','');setRestOrgCatalog(id,state)}
//status or market
//state --true / false
//id
	function statusOrMarket(elem,state,id){
		$.ajax({
	        url: "index.php?r=vendor/changecatalogprop",
	        type: "POST",
	        dataType: "json",
	        data: {'elem' : elem,'state' : state, 'id' : id},
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
	function setRestOrgCatalog(id,state){
		$.ajax({
	        url: "index.php?r=vendor/changesetcatalog",
	        type: "POST",
	        dataType: "json",
	        data: {'state' : state, 'id' : id, 'curCat' : $currentCatalog},
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
$("#products-list").on("pjax:complete", function() {
	console.log('продукт обновлен');
    //var searchInput = $("#search-string");
    //var strLength = searchInput.val().length * 2;
    //searchInput.focus();
    //searchInput[0].setSelectionRange(strLength, strLength);
});
$("#products-list").on("change keyup paste cut", "input", function() {
$("#search-form").submit();	
})
$("#exportToXls").on("click", function() {
	$.ajax({
	        url: "index.php?r=vendor/export-base-catalog-to-xls",
	        //cache: false,
	        success: function(response) {
		        console.log('ok')
		    },
		    failure: function(errMsg) {
	            console.log(errMsg);
	        }
	});
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
			        location.reload(); 
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