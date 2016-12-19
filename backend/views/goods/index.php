<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\bootstrap\Modal;
use common\assets\CroppieAsset;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CatalogBaseGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

CroppieAsset::register($this);
kartik\select2\Select2Asset::register($this);
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

$categoryUrl = Url::to(['category']);

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
  $('#goToCategory').on('click', function(e) {
        e.preventDefault();
        subcat = $("#subcat").val();
        if (subcat) { 
            document.location = "$categoryUrl&id=" + $("#subcat").val();
        }
  });
JS;
$this->registerJs($customJs, View::POS_READY);
?>
<div class="catalog-base-goods-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);   ?>  

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
                        'id' => $data['id']], [
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
            [
                'format' => 'raw',
                'attribute' => 'vendor_name',
                'value' => function ($data) {
                    return Html::a($data['vendor']['name'], ['organization/view', 'id' => $data['supp_org_id']]);
                },
                        'label' => 'Название организации',
                    ],
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
