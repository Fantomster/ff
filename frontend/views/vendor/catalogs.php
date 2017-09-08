<?php
use yii\widgets\Breadcrumbs;
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
$this->registerCss('
.text-info {
    color: #378a5f;
}
.hpanel .panel-body:hover {
-webkit-box-shadow: 0px 0px 26px -5px rgba(0,0,0,0.1);
-moz-box-shadow: 0px 0px 26px -5px rgba(0,0,0,0.1);
box-shadow: 0px 0px 26px -5px rgba(0,0,0,0.1);
}
');
?>
<?php
Modal::begin([
    'id' => 'setting-base-catalog',
    'clientOptions' => false,
    ]);
?>
<?php Modal::end(); ?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> Мои каталоги
        <small>Создавайте, добавляйте и редактируйте свои каталоги</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Мои каталоги'
        ],
    ])
    ?>
</section>
<section class="content">
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
                            <p class="small">Этот каталог содержит все ваши продукты доступные на MixCart</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Корректировка цен', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default btn-sm m-t']) ?>
                            <?= Html::a('<i class="fa fa-fw fa-clone"></i> Дублировать', ['vendor/step-1-clone', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default m-t btn-sm clone-catalog']) ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
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
                 <?= Html::a('<i class="fa fa-plus-circle"></i> Новый каталог', ['vendor/step-1'],['class'=>'btn btn-md fk-button']) ?>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= Html::label('&nbsp;', null, ['class' => 'label','style'=>'color:#555']) ?>
                        <div class="input-group">
                            <span class="input-group-addon">
                              <i class="fa fa-search"></i>
                            </span>
                        <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search'])?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?= Html::label('Ресторан', null, ['class' => 'label','style'=>'color:#555']) ?>
                        <?= Html::dropDownList('restaurant', null,
                            $relation,['prompt' => 'Все','class' => 'form-control','id'=>'restaurant']) ?>                        
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'catalog-list',]); ?>
                    <?php  
                    if(empty($arrCatalog)){ ?>   
                        <div class="empty">Ничего не найдено.</div>
                    <?php 
                    }else{
                        if($type==1){ ?>
                            <div class="panel-body">
                                <h4 class="text-info">Ресторан подключен к <strong>Главному каталогу</strong></h4>
                            </div>
                        <?php 
                        }else{
                            foreach($arrCatalog as $arrCatalogs){
                        ?>
                                <div class="hpanel" style="margin-bottom:15px;">
                                    <div class="panel-body">
                                        <div class="col-md-4 text-left">
                                        <?= Html::a('<h4 class="text-info"> '.$arrCatalogs->name.
                                                '</h4>', ['vendor/step-3-copy', 'id' => $arrCatalogs->id],['data-pjax'=>'0']) ?>
                                        <p class="small m-b-none">Создан: <?=Yii::$app->formatter->asDatetime($arrCatalogs->created_at, "php:j M Y"); ?></p>
                                        </div>
                                        <div class="col-md-8 text-right">
                                                <?php echo $link = SwitchBox::widget([
                                                'name' => 'status_'.$arrCatalogs->id,
                                                'checked' => $arrCatalogs->status==\common\models\Catalog::STATUS_OFF ? false : true,
                                                'clientOptions' => [
                                                    'onColor' => 'success',
                                                    'offColor' => 'default',
                                                    'onText'=>'Вкл',
                                                    'offText'=>'Выкл',
                                                    'baseClass'=>'bootstrap-switch',
                                                    'wrapperClass'=>'wrapper m-t bootstrap-switch-small',
                                                ],
                                                'class'=>'m-t'
                                            ]);
                                            ?>
                                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Корректировка цен', ['vendor/step-3-copy', 'id' => $arrCatalogs->id],['class'=>'btn btn-outline-default m-t btn-sm','data-pjax'=>'0','data-pjax'=>'0']) ?>
                                            <?= Html::a('<i class="fa fa-fw fa-clone"></i> Дублировать', ['vendor/step-1-clone', 'id' => $arrCatalogs->id],['class'=>'btn btn-outline-default m-t clone-catalog btn-sm','data-pjax'=>'0']) ?>
                                            <?= Html::button('<i class="fa fa-fw fa-trash-o"></i> Удалить', ['class' => 'btn btn-outline-danger m-t del btn-sm','name'=>'del_'.$arrCatalogs->id,'id'=>'del_'.$arrCatalogs->id]) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } 
                        }
                    }
                    ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>

</div>
</section>
<?php

$catalogsUrl = Url::to(['vendor/catalogs']);
$myCatalogDelCatalogUrl = Url::to(['vendor/mycatalogdelcatalog']);
$changeCatalogStatusUrl = Url::to(['vendor/changecatalogstatus']);

$customJs = <<< JS
var timer;
$('#search').on("keyup put paste change", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'POST',
        push: false,
        url: '$catalogsUrl',
        container: '#catalog-list',
        data: { searchString: $('#search').val(), restaurant: $('#restaurant').val() }
      })
   }, 700);
});
$("#restaurant").on("change", function() {
    $.pjax({
        type: 'POST',
        push: false,
        url: '$catalogsUrl',
        container: '#catalog-list',
        data: { searchString: $('#search').val(), restaurant: $('#restaurant').val() }       
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
$(location).attr('href','$catalogsUrl')
})
$(document).on('click', '.del', function (e){
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
	        url: "$myCatalogDelCatalogUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
			        console.log(response); 
			        //$.pjax.reload({container: "#catalog-list"});
                                $.pjax({
                                    type: 'POST',
                                    push: false,
                                    url: '$catalogsUrl',
                                    container: '#catalog-list',
                                    data: { searchString: $('#search').val(), restaurant: $('#restaurant').val() }       
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
        url: "$changeCatalogStatusUrl",
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
$(document).on('click', '.clone-catalog', function(e) {        
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
