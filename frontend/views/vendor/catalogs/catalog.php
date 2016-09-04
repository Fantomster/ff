<?php
use kartik\grid\gridview;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Category;
?>
<?php 
/*\moonland\phpexcel\Excel::export([
    'models' => Category::find()->all(),
    'columns' => ['name'],
	'headers' => ['name'=>'ss'],
]);*/
?>
<?php 
$this->title = 'Каталог';
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
    <li><a data-toggle="tab" href="#tabClients">Мои клиенты</a></li>
</ul>
<div class="tab-content">
    <div id="tabCatalog" class="tab-pane fade in active">
<div class="row">
	<div class="col-lg-12">
		<div class="hpanel">
            <div class="panel-body">
                <div class="btn-group m-t-xs m-r pull-right" placement="left">
	                    <button id="newProduct" data-target="#add-edit-product" data-toggle="modal" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Новый продукт</button>
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <li><a href="https://s3.amazonaws.com/imptest2014/productTemplate/Catalog+Template.xls" class="ng-binding">
	                            <i class="fa fa-list-alt m-r-xs"></i> Скачать шаблон</a>
	                        </li>
                        </ul>
                    </div>
                    <button style="margin-right: 10px;" type="button" class="btn btn-primary m-t-xs m-r pull-right"><i class="fa fa-download m-r-xs"></i> Выгрузить все продукты</button>
                    <button style="margin-right: 10px; margin-left: 10px;" class="btn btn-default m-t-xs m-r pull-right ng-binding" ng-click="tour.restart(true)"><i class="fa fa-question-circle"></i> Инструкция</button>
                    <?= Html::a('<i class="fa fa-reply m-r-xs"></i> Назад', ['vendor/catalogs'],['class'=>'btn btn-default m-t-xs m-r pull-right']) ?>
                </div>
            </div>
<?php 
$gridColumnsCatalog = [
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
                $link = Html::a('<i class="fa fa-trash m-r-xs"></i>', ['vendor/ajax-update-row-catalog', 'id' => $data->id], [
                    'data' => [
                    'target' => '#del-product',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                              ],
                    'class'=>'btn btn-danger'
                    
                ]);
                return $link;
            },
            
        ],
        [
            'attribute' => '',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = Html::a('<i class="fa fa-pencil m-r-xs"></i>', ['vendor/ajax-update-row-catalog', 'id' => $data->id], [
                    'data' => [
                    'target' => '#add-edit-product',
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
	'columns' => $gridColumnsCatalog,
]);
?>
	</div>
</div>
</div>
    <div id="tabClients" class="tab-pane fade">
    </div>
</div>
</div>
<?php
$customJs = <<< JS
$('input[type=checkbox]').on('switchChange.bootstrapSwitch', function (event, state) {	
var elem,e,id,state
elem = $(this).attr('name').substr(0, 6);
e = $(this).attr('name')
if(elem=="status")id = e.replace('status_','')
if(elem=="market")id = e.replace('marketplace_','')
//
//status or market
//state --true / false
//id
$.ajax({
        url: "index.php?r=vendor/changebasecatalogstatus",
        type: "POST",
        dataType: "json",
        data: {'elem' : elem,'state' : state, 'id' : id},
        cache: false,
        success: function(response) {
	        console.log(response)
	    },
	    failure: function(errMsg) {
            console.log(errMsg);
        }
	});
})
$('#invite').click(function(e){
e.preventDefault();	
})
JS;
$this->registerJs($customJs, View::POS_READY);
?>