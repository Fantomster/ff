<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\bootstrap\Modal;
use common\assets\CroppieAsset;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CatalogBaseGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
$this->title = 'Catalog Base Goods';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("           
                   // var uploadCrop;

		function readFile(input) {
 			if (input.files && input.files[0]) {
	            var reader = new FileReader();
	            
	            reader.onload = function (e) {
					$('.upload-avatar').addClass('ready');
                                        $('.upload-demo-wrap').css('opacity','1').css('z-index','198');
                                        console.log('ok');
	            	uploadCrop.croppie('bind', {
	            		url: e.target.result
	            	}).then(function(){
	            		console.log('jQuery bind complete');
	            	});
	            	
	            }
	            
	            reader.readAsDataURL(input.files[0]);
	        }
	        else {
		        swal('Sorry - your browser does not support the FileReader API');
		    }
		}

		$(document).on('change', '#upload', function () { readFile(this); });
		$(document).on('click', '.upload-result', function (ev) {
			uploadCrop.croppie('result', {
				type: 'canvas',
				size: 'viewport'
			}).then(function (resp) {
				popupResult({
					src: resp
				});
			});
		});
                
        "
);

$customJs = <<< JS
$("body").on("hidden.bs.modal", "#add-product-market-place", function() {
    $(this).data("bs.modal", null);
})
$("body").on("show.bs.modal", "#add-product-market-place", function() {
    $('#add-product-market-place>.modal-dialog').css('margin-top','13px');
})        
$("#add-product-market-place").on("click", ".edit", function() {
    var form = $("#marketplace-product-form");
    $('#loader-show').showLoading();
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $('#loader-show').hideLoading();
            form.replaceWith(result);
        $.pjax.reload({container: "#kv-unique-id-1"});
        });
        return false;
    });
  $('#add-product-market-place').removeAttr('tabindex');
JS;
$this->registerJs($customJs, View::POS_READY);
?>
<?php
Modal::begin([
    'id' => 'add-product-market-place',
    'clientOptions' => false,
    'size' => 'modal-lg',
]);
Modal::end();
?>
<div class="catalog-base-goods-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);   ?>
    <div class="col-md-12">
        <?php if($isEditable): ?>
            <?=
            Modal::widget([
                'id' => 'add-product',
                'clientOptions' => ['class' => 'pull-right'],
                'toggleButton' => [
                    'label' => '<i class="fa fa-plus-circle"></i> Новый товар',
                    'tag' => 'a',
                    'data-target' => '#add-product-market-place',
                    'class' => 'btn btn-fk-success btn-sm pull-right',
                    'href' => Url::to(['goods/ajax-update-product-market-place', 'id' => 0, 'supp_org_id'=>$id]),
                ],
            ])
            ?>
            <?=
            Modal::widget([
                'id' => 'importToXls',
                'clientOptions' => false,
                'size' => 'modal-md',
                'toggleButton' => [
                    'label' => '<i class="glyphicon glyphicon-import"></i> <span class="text-label">Загрузить каталог (XLS)</span>',
                    'tag' => 'a',
                    'data-target' => '#importToXls',
                    'class' => 'btn btn-outline-default btn-sm pull-right',
                    'href' => Url::to(['goods/import', 'id' => $id]),
                    'style' => 'margin-right:10px;',
                ],
            ])
            ?>
        <?php endif; ?>
        <?= Html::a("<i class=\"fa fa-fw fa-share\"></i> Распределить товары по категориям", ['category', 'vendor_id' => $id], ['class' => 'btn btn-outline-default btn-sm pull-right']) ?>
    </div>

    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true, // pjax is set to always true for this demo
        'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
        'columns' => [

            'id',
            'article',
//            'product',
            [
                'attribute' => 'product',
                'label' => 'Продукт',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:70px'],
                'headerOptions' => ['class' => 'text-center'],
                'value' => function ($data) {
            $link = Html::a($data['product'], ['ajax-update-product-market-place',
                        'id' => $data['id'], 'supp_org_id' => $data['supp_org_id']], [
                        'data' => [
                            'target' => '#add-product-market-place',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
            ]);
            return $link;
        },
            ],
            //'vendor.name',

                    'market_place',
                    // 'deleted',
                    // 'created_at',
                    // 'updated_at',
                    // 'supp_org_id',
                    // 'price',
                    // 'units',
                    'category_id',
                // 'note',
                // 'ed',
                // 'image',
                // 'brand',
                // 'region',
                // 'weight',
                // ['class' => 'yii\grid\ActionColumn'],
            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => ($isEditable) ? function ($data) {
                    $link = Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                        'class' => 'btn btn-xs btn-danger del-product',
                        'data' => ['id' => $data['id']],
                    ]);
                    return $link;
                } : '',
            ],
                ],
            ]);
            ?>
            <?php Pjax::end(); ?></div>
            <?php
        Modal::begin([
            'id' => 'add-product-market-place',
            'clientOptions' => false,
            'size' => 'modal-lg',
        ]);
        Modal::end();
        ?>

<?php
$catalogUrl = Url::to(['site/catalog', 'id' => $id]);
$deleteProductUrl = Url::to(['site/ajax-delete-product']);

$customJs = <<< JS
var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
   timer = setTimeout(function () {
       $.pjax({
        type: 'GET',
        push: true,
        timeout: 10000,
        url: '$catalogUrl',
        container: '#kv-unique-id-1',
        data: {searchString: $('#search').val()}
      })
   }, 700);
});

$(document).on("click",".del-product", function(e){
    var id = $(this).attr('data-id');
		$.ajax({
	        url: "$deleteProductUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        if(response.success){
			        $.pjax.reload({container: "#kv-unique-id-1"}); 
			        }else{
				    console.log('Что-то пошло не так');    
			        }
		        }	
		    });     
});

$("body").on("hidden.bs.modal", "#add-product-market-place", function() {
    $(this).data("bs.modal", null);
})
$("body").on("show.bs.modal", "#add-product-market-place", function() {
    $('#add-product-market-place>.modal-dialog').css('margin-top','13px');
})        
$(document).on("submit", "#marketplace-product-form", function(e) {
        e.preventDefault();
    var form = $("#marketplace-product-form");
    $('#loader-show').showLoading();
    $.post(
        form.attr("action"),
            form.serialize()
            )
            .done(function(result) {
            $('#loader-show').hideLoading();
            form.replaceWith(result);
        $.pjax.reload({container: "#kv-unique-id-1"});
        });
        return false;
    });
  $('#add-product-market-place').removeAttr('tabindex');
JS;
$this->registerJs($customJs, View::POS_READY);
