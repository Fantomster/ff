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
$this->title = Yii::t('app', 'franchise.views.catalog.my_catalogs', ['ru' => 'Мои каталоги']);
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
        <i class="fa fa-list-alt"></i> <?= Yii::t('app', 'franchise.views.catalog.vendors_catalogs', ['ru' => 'Каталоги поставщика']) ?> <?= $currentOrganization->name ?>
        <!--        <small><? //= Yii::t('app', 'franchise.views.catalog.edit_vendor', ['ru'=>'Создавайте, добавляйте и редактируйте каталоги поставщика']) ?></small>-->
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'franchise.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('app', 'franchise.views.catalog.my_catalogs_two', ['ru' => 'Мои каталоги'])
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="box-body">
                        <div class="col-md-6" style="padding-left: 30px; padding-top: 20px;">
                        <?php
                        if (empty($arrCatalog)) { ?>
                            <div class="empty"><?= Yii::t('app', 'franchise.views.catalog.nothing_found', ['ru' => 'Ничего не найдено']) ?>
                                .
                            </div>
                            <?php
                        } else {
                            if ($type == 1) { ?>
                                <div class="panel-body">
                                    <h4 class="text-info"><?= Yii::t('app', 'franchise.views.catalog.to_main', ['ru' => 'Ресторан подключен к <strong>Главному каталогу</strong>']) ?></h4>
                                </div>
                                <?php
                            } else {
                                ?>
                                <select class="form-control" onchange="window.location.href=this.options[this.selectedIndex].value">
                                    <option disabled><?= Yii::t('app', 'franchise.views.catalog.base_new.choose')?></option>
                                    <?php
                                    foreach ($arrCatalog as $arrCatalogs) {
                                        ?>
                                        <option value="<?= Url::toRoute(['catalog/index', 'id' => $currentOrganization->id, 'cat_id' => $arrCatalogs->id]) ?>" <?php if($arrCatalogs->id == $cat_id) echo 'selected' ?>>
                                            <?= $arrCatalogs->name ?>
                                        </option>

                                        <?php
                                    }
                                    ?>
                                </select>
                                <?php
                            }
                        }
                        ?>
                        </div>
                        <div style="clear: both;"></div>
                        <div>
                            <?= $table ?>
                        </div>
                    </div>
                </div>

            </div>
</section>
<?php

$catalogsUrl = Url::to(['catalog/index', 'vendor_id' => $currentOrganization->id]);
$myCatalogDelCatalogUrl = Url::to(['catalog/mycatalogdelcatalog']);
$changeCatalogStatusUrl = Url::to(['catalog/changecatalogstatus']);
$delCat = Yii::t('app', 'franchise.views.catalog.delete', ['ru' => 'Удалить каталог?']);
$del = Yii::t('app', 'franchise.views.catalog.delete_two', ['ru' => 'Удалить']);
$mess = Yii::t('app', 'franchise.views.catalog.will_unlink', ['ru' => 'Все рестораны будут отвязаны от текущего каталога']);
$cancel = Yii::t('app', 'franchise.views.catalog.cancel_two', ['ru' => 'Отмена']);
$double = Yii::t('app', 'franchise.views.catalog.create_double', ['ru' => 'Создать дубликат?']);
$copy = Yii::t('app', 'franchise.views.catalog.copy_create', ['ru' => 'Будет создана копия текущего каталога']);
$create = Yii::t('app', 'franchise.views.catalog.create_two', ['ru' => 'Создать']);


$customJs = <<< JS
var timer;

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
            title: "$delCat",
            message: "$mess", 
            buttons: {
                confirm: {
                    label: '$del',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$cancel',
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
            title: "$double",
            message: "$copy", 
            buttons: {
                confirm: {
                    label: '$create',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$cancel',
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
