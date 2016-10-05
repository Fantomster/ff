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
use yii\widgets\Pjax;
use common\models\Organization;
use common\models\User;
use dosamigos\switchinput\SwitchBox;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Мои каталоги';
?>
<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Каталоги</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php 
                $arrBaseCatalog = Catalog::GetCatalogs(\common\models\Catalog::BASE_CATALOG);
                foreach($arrBaseCatalog as $arrBaseCatalogs){
                ?>
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">Главный каталог</h4>', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id]) ?>
                            <p class="small">Этот каталог содержит все ваши продукты доступные на f-keeper</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default btn-sm m-t']) ?>
                            <?= Html::a('<i class="fa fa-fw fa-clone"></i>', ['vendor/step-1-clone', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default m-t btn-sm clone-catalog']) ?>
                        </div>
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
            </div>
            <!-- /.box-body -->
            <!--div class="box-footer clearfix">
              <span class="pull-right">5 каталогов</span>
            </div-->
            <!-- /.box-footer -->
          </div>
                <div class="box box-info">
            <div class="box-header with-border">
              <div class="box-title pull-left">
                <?= Html::a('Новый каталог', ['vendor/step-1'],['class'=>'btn btn-sm btn-fk-success']) ?>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= Html::dropDownList('restaurant', null,
                            ArrayHelper::map(common\models\Organization::find()->
                                where(['in', 'id', \common\models\RelationSuppRest::find()->
                                    select('rest_org_id')->
                                    where(['supp_org_id'=>$currentUser->organization_id,'status'=>'1'])])->all(),'id','name'),['prompt' => '','class' => 'form-control','id'=>'restaurant']) ?>
                        
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div id="pjaxgo">
                    <?php echo $this->render('catalogs/_listCatalog',['currentUser'=>$currentUser,'search'=>'','restaurant'=>''])  ?>
                </div>
            </div>
          </div>
</div>

<?php
$customJs = <<< JS
var timer;
$('#search').on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.ajax({
        type: 'POST',
        url: 'index.php?r=vendor/list-catalog',
        container: '#pjaxgo',
        data: { search: $('#search').val(), restaurant: $('#restaurant').val() },
        success: function(response) {
        $('#pjaxgo').html(response)    
        }
      })
   }, 700);
});
$("#restaurant").on("change", function() {
    $.ajax({
        type: 'POST',
        url: 'index.php?r=vendor/list-catalog',
        container: '#pjaxgo',
        data: { search: $('#search').val(), restaurant: $('#restaurant').val() },
        success: function(response) {
        $('#pjaxgo').html(response)    
        }
      })
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
$("body").on("hidden.bs.modal", function() {
    $(this).data("bs.modal", null);
});
        

$('#viewBaseCatalog').click(function (e){
$(location).attr('href','index.php?r=vendor/catalogs')
})
$('.del').live("click", function (e){
	var id = $(this).attr('id').replace('del_','');
	bootbox.confirm({
            title: "Удалить каталог?",
            message: "Все рестораны будут отвязаны от текущего каталога", 
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
	        url: "index.php?r=vendor/mycatalogdelcatalog",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
			        console.log(response); 
			        //$.pjax.reload({container: "#catalog-list"});
                                $.ajax({
                                    type: 'POST',
                                    url: 'index.php?r=vendor/list-catalog',
                                    container: '#pjaxgo',
                                    data: { search: $('#search').val(), restaurant: "1" },
                                    //dataType: 'application/json',
                                    success: function(response) {
                                    $('#pjaxgo').html(response)    
                                    }
                                  })
			        }else{
				    console.log('Что-то пошло не так');    
			        }
		        }	
		    });
		}else{
		console.log('cancel');	
		}
	}})
});
$('input[type=checkbox]').live('switchChange.bootstrapSwitch', function (event, state) {	
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
$(".clone-catalog").live('click', function(e) {        
    e.preventDefault();
    elem = $(this)
    Url = $(this).attr('href')
    bootbox.confirm({
            title: "Создать дубликат?",
            message: "Будет создана копия текущего каталога", 
            buttons: {
                confirm: {
                    label: 'Создать',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Отмена',
                    className: 'btn-default'
                }
            },
            className: "success-fk",
            callback: function(result) {
		if(result){
           location.href = Url;
        }
    }})
});        
JS;
$this->registerJs($customJs, View::POS_READY);
?>