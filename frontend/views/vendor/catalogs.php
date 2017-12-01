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
$this->title = Yii::t('message', 'frontend.views.vendor.my_catalogs', ['ru'=>'Мои каталоги']);
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
        <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.vendor.my_catalogs_two', ['ru'=>'Мои каталоги']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.create_catalogs', ['ru'=>'Создавайте, добавляйте и редактируйте свои каталоги']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.my_catalogs_three', ['ru'=>'Мои каталоги'])
        ],
    ])
    ?>
</section>
<section class="content">
<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.catalogs', ['ru'=>'Каталоги']) ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php 
                $arrBaseCatalog = Catalog::GetCatalogs(\common\models\Catalog::BASE_CATALOG);
                foreach($arrBaseCatalog as $arrBaseCatalogs){

                        $catCurrency = \common\models\Currency::findOne(['id' => $arrBaseCatalogs->currency_id]);
                ?>
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">' . Yii::t('message', 'frontend.views.vendor.main_catalog', ['ru'=>'Главный каталог']) . ' </h4>', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id]) ?>

                            <p class="small">
                                <?= Yii::t('message', 'frontend.views.vendor.currency', ['ru'=>'Валюта каталога:']) ?> <?php  echo $catCurrency->text.' (' . $catCurrency->iso_code. ')'; ?>
								 <br>
                            	<?= Yii::t('message', 'frontend.views.vendor.this_cat', ['ru'=>'Этот каталог содержит все ваши продукты доступные на MixCart']) ?>
                            </p>

                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> ' . Yii::t('message', 'frontend.views.vendor.prices', ['ru'=>'Корректировка цен']) . ' ', ['vendor/basecatalog', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default btn-sm m-t']) ?>
                            <?= Html::a('<i class="fa fa-fw fa-clone"></i> ' . Yii::t('message', 'frontend.views.vendor.doublicate', ['ru'=>'Дублировать']) . ' ', ['vendor/step-1-clone', 'id' => $arrBaseCatalogs->id],['class'=>'btn btn-default m-t btn-sm clone-catalog']) ?>
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
                 <?= Html::a('<i class="fa fa-plus-circle"></i> ' . Yii::t('message', 'frontend.views.vendor.new_cat', ['ru'=>'Новый каталог']) . ' ', ['vendor/step-1'],['class'=>'btn btn-md fk-button']) ?>
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
                        <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>Yii::t('message', 'frontend.views.vendor.search_three', ['ru'=>'Поиск']),'id'=>'search'])?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?= Html::label(Yii::t('message', 'frontend.views.vendor.rest_two', ['ru'=>'Ресторан']), null, ['class' => 'label','style'=>'color:#555']) ?>
                        <?= Html::dropDownList('restaurant', null,
                            $relation,['prompt' => Yii::t('message', 'frontend.views.vendor.all_three', ['ru'=>'Все']),'class' => 'form-control','id'=>'restaurant']) ?>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'catalog-list',]); ?>
                    <?php  
                    if(empty($arrCatalog)){ ?>   
                        <div class="empty"><?= Yii::t('message', 'frontend.views.vendor.nothing_found', ['ru'=>'Ничего не найдено.']) ?></div>
                    <?php 
                    }else{
                        if($type==1){ ?>
                            <div class="panel-body">
                                <h4 class="text-info"><?= Yii::t('message', 'frontend.views.vendor.rest_to_main', ['ru'=>'Ресторан подключен к <strong>Главному каталогу</strong>']) ?></h4>
                            </div>
                        <?php 
                        }else{
                            foreach($arrCatalog as $arrCatalogs){
                                $catCurrency = \common\models\Currency::findOne(['id' => $arrCatalogs->currency_id]);
                        ?>
                                <div class="hpanel" style="margin-bottom:15px;">
                                    <div class="panel-body">
                                        <div class="col-md-4 text-left">
                                        <?= Html::a('<h4 class="text-info"> '.$arrCatalogs->name.
                                                '</h4>', ['vendor/step-3-copy', 'id' => $arrCatalogs->id],['data-pjax'=>'0']) ?>
                                        <p class="small m-b-none">
                                            <?= Yii::t('message', 'frontend.views.vendor.currency_two', ['ru'=>'Валюта каталога:']) ?> <?php  echo ' '.$catCurrency->text.' (' . $catCurrency->iso_code. ')'; ?> <br>
                                        	<?= Yii::t('message', 'frontend.views.vendor.created_at', ['ru'=>'Создан:']) ?> <?=Yii::$app->formatter->asDatetime($arrCatalogs->created_at, "php:j M Y"); ?>
                                        </p>
                                        </div>
                                        <div class="col-md-8 text-right">
                                                <?php echo $link = SwitchBox::widget([
                                                'name' => 'status_'.$arrCatalogs->id,
                                                'checked' => $arrCatalogs->status==\common\models\Catalog::STATUS_OFF ? false : true,
                                                'clientOptions' => [
                                                    'onColor' => 'success',
                                                    'offColor' => 'default',
                                                    'onText'=>Yii::t('message', 'frontend.views.vendor.on', ['ru'=>'Вкл']),
                                                    'offText'=>Yii::t('message', 'frontend.views.vendor.off', ['ru'=>'Выкл']),
                                                    'baseClass'=>'bootstrap-switch',
                                                    'wrapperClass'=>'wrapper m-t bootstrap-switch-small',
                                                ],
                                                'class'=>'m-t'
                                            ]);
                                            ?>
                                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> ' . Yii::t('message', 'frontend.views.vendor.prices_correct', ['ru'=>'Корректировка цен']) . ' ', ['vendor/step-3-copy', 'id' => $arrCatalogs->id],['class'=>'btn btn-outline-default m-t btn-sm','data-pjax'=>'0','data-pjax'=>'0']) ?>
                                            <?= Html::a('<i class="fa fa-fw fa-clone"></i> ' . Yii::t('message', 'frontend.views.vendor.doublicate_two', ['ru'=>'Дублировать']) . ' ', ['vendor/step-1-clone', 'id' => $arrCatalogs->id],['class'=>'btn btn-outline-default m-t clone-catalog btn-sm','data-pjax'=>'0']) ?>
                                            <?= Html::button('<i class="fa fa-fw fa-trash-o"></i> ' . Yii::t('message', 'frontend.views.vendor.delete_two', ['ru'=>'Удалить']) . ' ', ['class' => 'btn btn-outline-danger m-t del btn-sm','name'=>'del_'.$arrCatalogs->id,'id'=>'del_'.$arrCatalogs->id]) ?>
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
$one = Yii::t('message', 'frontend.views.vendor.del_cat_two', ['ru'=>"Удалить каталог?"]);
$two = Yii::t('message', 'frontend.views.vendor.all_rests', ['ru'=>"Все рестораны будут отвязаны от текущего каталога"]);
$three = Yii::t('message', 'frontend.views.vendor.del_three', ['ru'=>'Удалить']);
$four = Yii::t('message', 'frontend.views.vendor.cancel_three', ['ru'=>'Отмена']);
$five = Yii::t('message', 'frontend.views.vendor.cr_doublicate', ['ru'=>"Создать дубликат?"]);
$six = Yii::t('message', 'frontend.views.vendor.copy_cat', ['ru'=>"Будет создана копия текущего каталога"]);
$seven = Yii::t('message', 'frontend.views.vendor.create', ['ru'=>'Создать']);
$eight = Yii::t('message', 'frontend.views.vendor.cancel_four', ['ru'=>'Отмена']);

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
            title: "$one",
            message: "$two", 
            buttons: {
                confirm: {
                    label: '$three',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$four',
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
$(document).on('switchChange.bootstrapSwitch', 'input[type=checkbox]', function (event, state) {	
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
            title: "$five",
            message: "$six", 
            buttons: {
                confirm: {
                    label: '$seven',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$eight',
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
