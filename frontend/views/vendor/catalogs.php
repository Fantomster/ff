<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\db\Query;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use common\models\CatalogBaseGoods;
use common\models\RelationSuppRest;
use common\models\Catalog;
use common\models\Organization;
use common\models\User;
use dosamigos\switchinput\SwitchBox;
use yii\data\Pagination;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Мои каталоги (Поставщик)';
//$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('
.hpanel .panel-body {background: #fff;
    border: 1px solid #e4e5e7;
    border-radius: 2px;
    padding: 20px;
    position: relative;}
.m-t {
    margin-top: 15px;
}
');
$this->registerCss('.panel-body {padding: 15px;}h1, .h1, h2, .h2, h3, .h3 {margin-top: 10px;}');
?>
<div class="catalog-index">
    <div class="panel-body">
<?= Html::a('<i class="fa fa-fw fa-plus"></i> Новый каталог', ['vendor/step-1'],['class'=>'btn btn-lg btn-primary pull-right']) ?>
        <h3 class="font-light">
            <i class="fa fa-list-alt"></i> Мои Каталоги
        </h3> 
    </div>
<?php 
$arrBaseCatalog = Catalog::GetCatalogs(\common\models\Catalog::BASE_CATALOG);
foreach($arrBaseCatalog as $arrBaseCatalogs){
?>
<div class="hpanel">
    <div class="panel-body">
        <div class="pull-right text-right">
	                <?= Html::a('Просмотр/Редактирование', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default m-t']) ?>
                        <?= Html::a('<i class="fa fa-fw fa-clone"></i> Дубликат', ['vendor/step-1-clone', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default m-t clone-catalog']) ?>
                    
        </div>
                <?= Html::a('<h4 class="m-b-xs text-info">Главный каталог <sup class="text-success"><i class="fa fa-user"></i> '.\common\models\relationSuppRest::row_count($arrBaseCatalogs->id).'</sup></h4>', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id]) ?>
                <p class="small">Этот каталог содержит все ваши продукты доступные на f-keeper</p>
    </div>
</div>
<?php
}
Modal::begin([
    'id' => 'setting-base-catalog',
    'clientOptions' => false,
    ]);
?>
<?php Modal::end(); ?>	

<?php // Html::input('text', 'searchToCatalogs', null, ['class' => 'form-control','placeholder'=>'Умный поиск']) ?> 


<?php 	
$arrCatalog = Catalog::GetCatalogs(\common\models\Catalog::CATALOG);	
if(!empty($arrCatalog)){
?>
<div class="panel-body">
    <h4 class="font-light">
        Каталоги
    </h4> 
</div>    
<?php }
foreach($arrCatalog as $arrCatalogs){?>
    
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-lg-12">
                <div class="hpanel" style="margin-bottom:0px;">
                                <div class="panel-body">
                        <div class="pull-right text-right">
                                <?php echo $link = SwitchBox::widget([
                                'name' => 'status_'.$arrCatalogs->id,
                                'checked' => $arrCatalogs->status==Catalog::STATUS_OFF ? false : true,
                                'clientOptions' => [
                                    'onColor' => 'success',
                                    'offColor' => 'default',
                                    'onText'=>'Вкл',
                                    'offText'=>'Выкл',
                                    'baseClass'=>'bootstrap-switch',
                                    'wrapperClass'=>'wrapper m-t',
                                ],
                                'class'=>'m-t'
                            ]);
                            ?>
                            <?= Html::a('Просмотр/Редактирование', ['vendor/step-3', 'id' => $arrCatalogs->id],['class'=>'btn btn-default m-t']) ?>
                            <?= Html::a('<i class="fa fa-fw fa-clone"></i> Дубликат', ['vendor/step-1-clone', 'id' => $arrCatalogs->id],['class'=>'btn btn-default m-t clone-catalog']) ?>
                            <?= Html::button('<i class="fa fa-fw fa-trash-o"></i>', ['class' => 'btn btn-danger m-t del','name'=>'del_'.$arrCatalogs->id,'id'=>'del_'.$arrCatalogs->id]) ?>
                            </div>
                        <?= Html::a('<h4 class="m-b-xs text-info"> '.$arrCatalogs->name.' <sup class="text-success"><i class="fa fa-user"></i> '.\common\models\relationSuppRest::row_count($arrCatalogs->id).'</sup></h4>', ['vendor/step-3', 'id' => $arrCatalogs->id]) ?>

                        <p class="small m-b-none">Создан: <?=$arrCatalogs->created_at ?></p>
                        </p>
                    </div>
                </div>
            </div>
        </div>
<?php } ?>
</div>

<?php
$customJs = <<< JS
$("body").on("hidden.bs.modal", function() {
    $(this).data("bs.modal", null);
});
$('#viewBaseCatalog').click(function (e){
$(location).attr('href','index.php?r=vendor/catalogs')
})
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
$('input[type=checkbox]').on('switchChange.bootstrapSwitch', function (event, state) {	
var e,id,state
e = $(this).attr('name')
id = e.replace('status_','')
    $.ajax({
        url: "index.php?r=vendor/changecatalogstatus",
        type: "POST",
        dataType: "json",
        data: {'state' : state, 'id' : id},
        cache: false,
        success: function(response) {
	        console.log(response)
	    },
	    failure: function(errMsg) {
            console.log(errMsg);
        }
    });
})
$(".clone-catalog").click(function(e) {        
    e.preventDefault();
    elem = $(this)
    Url = $(this).attr('href')
    bootbox.confirm("<h3>Создать дубликат каталога?</h3>", function(result) {
        if(result)
        {
           location.href = Url;
        }
    })
});        
JS;
$this->registerJs($customJs, View::POS_READY);
?>