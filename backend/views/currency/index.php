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
kartik\select2\Select2Asset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
$this->title = Yii::t('app', 'Валюты');
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

$categoryUrl = Url::to(['category', 'id' => '']);

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
            document.location = "$categoryUrl" + $("#subcat").val();
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
        'filterModel'  => $searchModel,
        'pjax'         => true, // pjax is set to always true for this demo
        'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
        'columns'      => [
            [
                'format'    => 'raw',
                'attribute' => 'text',
                'label'     => 'Название',
            ],
            [
                'format'    => 'raw',
                'attribute' => 'iso_code',
                'label'     => 'ISO код',
            ],
            [
                'format'    => 'raw',
                'attribute' => 'num_code',
                'label'     => 'Числовой код',
            ],
            [
                'format'    => 'raw',
                'attribute' => 'is_active',
                'filter'    => [0 => 'Не активна', 1 => 'Активна'],
                'value'     => function ($data) {
                    return ($data['is_active']) ? 'Активна' : '<span style="color: red;">Не активна</span>';
                },
                'label'     => 'Статус',
            ],

            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{edit}',
                'buttons'  => [
                    'edit' => function ($url, $model) {
                        $customurl = Yii::$app->getUrlManager()->createUrl(['currency/update', 'id' => $model['id']]);
                        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0']);
                    },
                ],
            ],
        ],
    ]);
    ?>
    <?php Pjax::end(); ?></div>
<?php
Modal::begin([
    'id'            => 'add-product-market-place',
    'clientOptions' => false,
    'size'          => 'modal-lg',
]);
Modal::end();
?>
