<?php

use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use common\models\CatalogBaseGoods;
use common\models\RelationSuppRest;
use common\models\Catalog;
use common\models\Organization;
use common\models\User;
use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Мои каталоги (Поставщик)';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('
.hpanel .panel-body {background: #fff;
    border: 1px solid #e4e5e7;
    border-radius: 2px;
    padding: 20px;
    position: relative;}
.panel-body {padding: 15px;}
.m-t {
    margin-top: 15px;
}
');	
?>

<h1><?= Html::encode($this->title) ?></h1>
<div class="catalog-index">
<div class="row">
	<div class="col-lg-12">
		<h2 style="float: left">Базовый каталог</h2>
			<?=
			Modal::widget([
			    'id' => 'create-catalog',
			    'clientOptions' => false,
			    'toggleButton' => [
			        'label' => '<i class="fa fa-fw fa-plus"></i> Новый каталог',
			        'tag' => 'a',
			        'data-target' => '#create-catalog',
			        'class' => 'btn btn-primary',
			        'href' => Url::toRoute(['/vendor/createcatalog']),
			        'style' => 'float:right',
			    ],
			])
			?>
	</div>
</div>


<?php 
$arrBaseCatalog = Catalog::GetBaseCatalog();	
foreach($arrBaseCatalog as $arrBaseCatalogs){
?>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel" style="margin-bottom:0px;">
			<div class="panel-body">
                <div class="pull-right text-right">
	                <?= Html::button('<i class="fa fa-fw fa-file-text-o" aria-hidden="true"></i> Импорт', ['class' => 'btn btn-default m-t', 'name' => 'importBaseCatalog','id' => 'importBaseCatalog']) ?>
	                <?= Html::button('Просмотр/Редактирование', ['class' => 'btn btn-default m-t', 'name' => 'viewBaseCatalog','id' => 'viewBaseCatalog']) ?>
                    <?= Html::button('<i class="fa fa-fw fa-clone"></i> Дубликат', ['class' => 'btn btn-default m-t', 'name' => 'cloneBaseCatalog','id' => 'cloneBaseCatalog']) ?>
                </div>
                <?= Html::a('<h4 class="m-b-xs text-info">Базовый каталог</h4>', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id]) ?>
                <p class="small">Этот каталог содержит все ваши продукты доступные на f-keeper</p>
            </div>
        </div>
    </div>
</div>
<?php 
$cat_base_id = $arrBaseCatalogs->id;
} ?>
<?php
Modal::begin([
    'id' => 'setting-base-catalog',
    'clientOptions' => false,
    ]);
?>
<?php Modal::end(); ?>	
<h2>Шаблоны каталогов</h2>
<div class="input-group" style="margin-bottom: 15px;">
<?= Html::input('text', 'searchToCatalogs', null, ['class' => 'form-control','placeholder'=>'Умный поиск']) ?> 
</div>
<?php $form = ActiveForm::begin(['id'=>'MyCatalogFormSend']);?>
<?php 
$arrCatalog = RelationSuppRest::GetCatalogs();	
foreach($arrCatalog as $arrCatalogs){
?>
		<div class="row" style="margin-bottom: 15px;">
		    <div class="col-lg-12">
		        <div class="hpanel" style="margin-bottom:0px;">
					<div class="panel-body">
		                <div class="pull-right text-right">
			                <?= Html::button('<i class="fa fa-fw fa-trash-o"></i> Удалить', ['class' => 'btn btn-danger m-t del','name'=>'del_'.$arrCatalogs->cat_id,'id'=>'del_'.$arrCatalogs->cat_id]) ?>
		                    <?php if($arrCatalogs->status==1){
			                     echo Html::button('Активный', ['class' => 'btn btn-success m-t enDs','data-status'=>'1','name'=>'cat_'.$arrCatalogs->id,'id'=>'cat_'.$arrCatalogs->id]);}else{
				                 echo Html::button('Отключен', ['class' => 'btn btn-default m-t enDs','data-status'=>'0','name'=>'cat_'.$arrCatalogs->id,'id'=>'cat_'.$arrCatalogs->id]);}
		                    ?>
			                <?= Html::button('Просмотр/Редактирование', ['class' => 'btn btn-default m-t','name'=>'view_'.$arrCatalogs->id,'id'=>'view_'.$arrCatalogs->id]) ?>
		                    <?= Html::button('<i class="fa fa-fw fa-clone"></i> Дубликат', ['class' => 'btn btn-default m-t','name'=>'clone_'.$arrCatalogs->id,'id'=>'clone_'.$arrCatalogs->id]) ?>
		                    
			            </div>
		                <a href="#/view_catalog"><h4 class="m-b-xs text-info"><?php echo Catalog::getNameCatalog($arrCatalogs->cat_id)->name; ?></h4></a>
		                <p class="small">Инфа</p>
		                </p>
		            </div>
		        </div>
		    </div>
		</div>
<?php } ?>
<?php ActiveForm::end();?>
</div>

<div id="modal_baseCatalog" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <div class="text-center">
	    <h3></h3>
		<h3 class="modal-title">
			Базовый каталог
		</h3>
	</div>
      </div>
      <div class="modal-body">
	   <div id="CreateCatalog">
		   <?php 
			    $searchModel = new CatalogBaseGoods();
			    $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$cat_base_id);
			?>
			<?= DataTables::widget([
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
					'searching' => false,
					'ordering' =>  false,
				    'paging' => false,
				    "info"=>false,
				    "responsive"=>true, 
				],
			]);?>
	   </div>   
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
        <button id="invite" type="button" class="btn btn-info">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<?php
$this->registerCssFile('modules/handsontable/dist/handsontable.full.css');
$this->registerCssFile('modules/handsontable/dist/pikaday/pikaday.css');
$this->registerjsFile('modules/handsontable/dist/pikaday/pikaday.js');
$this->registerjsFile('modules/handsontable/dist/moment/moment.js');
$this->registerjsFile('modules/handsontable/dist/numbro/numbro.js');
$this->registerjsFile('modules/handsontable/dist/zeroclipboard/ZeroClipboard.js');
$this->registerjsFile('modules/handsontable/dist/numbro/languages.js');
$this->registerJsFile('modules/handsontable/dist/handsontable.js');
$customJs = <<< JS
/*
$('#viewBaseCatalog').click(function (e){
  $('#modal_baseCatalog').modal('show');
});*/
$("body").on("hidden.bs.modal", function() {
    $(this).data("bs.modal", null);
});
$('.del').click(function (e){
	var id = $(this).attr('id').replace('del_','');
	bootbox.confirm("<h3>Удалить этот каталог?</h3><p class='small'>Все привязки каталога к клиенту тоже удаляться</p>", function(result) {
		if(result){
			$.ajax({
	        url: "index.php?r=vendor/mycatalogdelcatalog",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
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
});
$('.enDs').click(function (e){
	$(this).attr('disabled','disabled');
	var elem = $(this);
	var id = elem.attr('id').replace('cat_','');
	var status = elem.attr('data-status');
	bootbox.confirm("<h3>Подтвердите действие</h3>", function(result) {
		if(result){
	
	$.ajax({
        url: "index.php?r=vendor/changestatus",
        type: "POST",
        dataType: "json",
        data: {'id' : id,'status' : status},
        cache: false,
        success: function(response) {
	        if(response.success){
		        elem.removeAttr('disabled')
		      if(response.status==0){
			    elem.attr('data-status','0');
			    elem.removeClass('btn-success').addClass('btn-default').html('Отключен');
			  	console.log(response.status);
		      }else{
			     elem.attr('data-status','1');
			     elem.removeClass('btn-default').addClass('btn-success').html('Активный');
			     console.log(response.status); 
		      }
	        }
	    },
	    failure: function(errMsg) {
            console.log(errMsg);
        }
	});
	}else{
	elem.removeAttr('disabled');
	}
});
});
/*
$('#setting-base-cat').on('click', function() {
        var id = $(this).attr('data-id');
		$.ajax({
            url: 'index.php?r=vendor/settingbasecatalog',
            data: {'id' : id},
            type: "POST",
			dataType: "json",
			cache: false,
        }).success(function(response) {
	    $('#myModal').modal('show');
	    });
	});    
*/	        
JS;
$this->registerJs($customJs, View::POS_READY);
?>