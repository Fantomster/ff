<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use common\assets\CroppieAsset;

CroppieAsset::register($this);

$this->registerJs("           
                   // var uploadCrop;

		function readFile(input) {
 			if (input.files && input.files[0]) {
	            var reader = new FileReader();
	            
	            reader.onload = function (e) {
					$('.upload-avatar').addClass('ready');
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
                $(document).on('click', '.saveAva', function() {
                    $('#loader-show').showLoading();
                    var form = $('#avatarForm');
                    $.post(
                        form.attr('action'),
                        form.serialize()
                    )
                    .done(function(result) {
                        form.replaceWith(result);
                        $('#loader-show').hideLoading();
                    });
                });
                $(document).on('click', '#deleteAvatar', function() {
                    $('#loader-show').showLoading();
                    $.post(
                        '".Url::to(['settings/ajax-delete-avatar'])."'
                    )
                    .done(function(result) {
                        $('.avatar').attr('src', result); 
                        $('#loader-show').hideLoading();
                    });
                });
                $(document).on('hidden.bs.modal', '#setAvatar', function() {
                    $('.avatar').attr('src', $('#newAvatar').attr('src')); 
                    $(this).data('bs.modal', null);
                });
        "
);
$this->title = "Личные настройки";
?>
<style>
    .upload-demo .upload-demo-wrap,
    .upload-demo .upload-result,
    .upload-demo.ready .upload-msg {
        display: none;
    }
    .upload-demo.ready .upload-demo-wrap {
        display: block;
    }
    .upload-demo.ready .upload-result {
        display: inline-block;    
    }
    .upload-demo-wrap {
        width: 300px;
        height: 300px;
        margin: 0 auto;
    }
    .upload-msg {
        text-align: center;
        padding: 50px;
        font-size: 22px;
        color: #aaa;
        width: 260px;
        margin: 50px auto;
        border: 1px solid #aaa;
    }

</style>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Личные настройки
        <small>Информация обо мне</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Личные',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <div class="box box-header">
            <img width="90" height="90" src="<?= $profile->avatarUrl ?>" class="avatar"/>
            <?=
            Modal::widget([
                'id' => 'setAvatar',
                'clientOptions' => false,
                'toggleButton' => [
                    'label' => '<i class="icon fa fa-user-plus"></i>  Сменить аватар',
                    'tag' => 'a',
                    'data-target' => '#setAvatar',
                    'class' => 'btn btn-success',
                    'href' => Url::to(['/settings/ajax-change-avatar']),
                ],
            ])
            ?>
            <?=
            Html::a('<i class="fa fa-trash m-r-xxs"></i> Удалить аватар', '#', [
                                    'class' => 'btn btn-danger',
                                    'id' => 'deleteAvatar',
                        ]);
            ?>
        </div>
        <div class="box-body">
            <?php Pjax::begin(['enablePushState' => false, 'id' => 'personalSettings', 'timeout' => 5000]); ?>
            <?php
            $form = ActiveForm::begin([
                        'id' => 'personalSettingsForm',
                        'enableAjaxValidation' => false,
                        'options' => [
                            'data-pjax' => true,
                        ],
                        'method' => 'get',
            ]);
            ?>
            <?= $profile->full_name ?>
            <?php
            ActiveForm::end();
            ?>
            <?php Pjax::end(); ?>
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
    </div>
</section>
