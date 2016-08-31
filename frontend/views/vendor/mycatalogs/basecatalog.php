<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use fedemotta\datatables\DataTables;
?>
<?php 
$this->title = 'Базовый каталог';
$this->params['breadcrumbs'][] = $this->title;	
?>
<div class="catalog-index">
<div class="row">
	<div class="col-lg-12">
		<div class="hpanel">
            <div class="panel-body">
                <div class="btn-group m-t-xs m-r pull-right" placement="left">
                    <button type="button" class="btn btn-info" href="#/edit_catalog"><i class="fa fa-pencil m-r-xs"></i> Редактирование</button>
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu m-t-sm">
                  	    <li>
                    	   <a>
                      	    <i class="fa fa-upload m-r-xs"></i>
                      	    Загрузить продукты
                    	   </a>
                  	    </li>
                            <li class="divider"></li>
                            <li><a href="https://s3.amazonaws.com/imptest2014/productTemplate/Catalog+Template.xls" class="ng-binding"><i class="fa fa-list-alt m-r-xs"></i> Скачать шаблон</a></li>
                        </ul>
                    </div>
                    <button style="margin-right: 10px;" type="button" class="btn btn-primary btn-outline m-t-xs m-r pull-right ng-binding" ng-click="exportAllProductsToCSV()"><i class="fa fa-download m-r-xs"></i> Выгрузить все продукты</button>
                    <button style="margin-right: 10px;" class="btn btn-default m-t-xs m-r pull-right ng-binding" ng-click="tour.restart(true)"><i class="fa fa-question-circle"></i> Инструкция</button>
                    <h3><i class="fa fa-list-alt"></i> Базовый каталог</h3>
                </div>
            </div>		
		<div style="float: right"><h2><?= Html::a('Назад', ['vendor/mycatalogs'],['class'=>'btn btn-default']) ?></h2></div>
<?=DataTables::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'columns' => [
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
	],
	'clientOptions' => [
	//'searching' => false,
	'ordering' =>  false, 
    "dom"=> 'lfTrtip',
    "tableTools"=>[
        "aButtons"=> [  
            [
            "sExtends"=> "copy",
            "sButtonText"=> Yii::t('app',"Copy to clipboard")
            ],[
            "sExtends"=> "csv",
            "sButtonText"=> Yii::t('app',"Save to CSV")
            ],[
            "sExtends"=> "xls",
            "oSelectorOpts"=> ["page"=> 'current']
            ],[
            "sExtends"=> "pdf",
            "sButtonText"=> Yii::t('app',"Save to PDF")
            ],[
            "sExtends"=> "print",
            "sButtonText"=> Yii::t('app',"Print")
            ],
        ]
    ],
	'paging' => false,
	"info"=>false,
	"responsive"=>true, 
	],
]);
?>
	</div>
</div>
</div>