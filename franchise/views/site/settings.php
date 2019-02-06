<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
$this->title = Yii::t('app', 'franchise.views.site.settings_two', ['ru'=>'Настройки']);
$this->registerJs(
        '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                $.pjax.reload({container: "#settingsInfo"});      
            });
            $(".settings").on("change paste keyup", ".form-control, input", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
            $(".settings").on("click", ".country", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
            $(document).on("submit", "#generalSettings", function(e) {
                $("#cancelOrg").prop( "disabled", true );
                $("#saveOrg").prop( "disabled", true );
            });
        });'
);
$this->registerJs("           
		function readFile(input) {
 			if (input.files && input.files[0]) {
	            var reader = new FileReader();
	            
	            reader.onload = function (e) {
                                $('.upload-avatar').addClass('ready');
                                $('.upload-demo-wrap').css('opacity','1').css('z-index','198');
                                console.log('ok');
                                uploadCrop = $('#upload-avatar').croppie({
                                    viewport: {
                                            width: 420,
                                            height: 236,
                                            type: 'square'
                                    },
                                    update: function(){
                                        uploadCrop.croppie('result', {type:'canvas'}).then(function (resp) {
                                            $('#image-crop-result').val(resp);
                                        });
                                    },
                                    enableExif: true
                                });
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

		$(document).on('change', '#upload', function () { 
                    size = $('#upload').get(0).files[0].size;
                    if (size <= 2097152) {
                        readFile(this); 
                        $('#uploadAvatar').toggle();
                        $('#stub').toggle();
                    }
                });
                
        "
);
$this->registerCss("
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
    position:absolute;
    width: 420px;
    height: 236px;
    border-radius: 0%;
    top: 66px;
    margin: 0 auto;
    left: 0;
    right: 0;
    opacity:0;
}
.cr-boundary{border-radius:0%}
.croppie-container .cr-slider-wrap {
    margin: 20px auto;
}
#upload-avatar{border-radius:0%}
.cr-viewport{border-radius:0%}
.croppie-container .cr-viewport {
    border: 0;
}
.intl-tel-input {width: 100%;display: table-cell;}
        ");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('app', 'franchise.views.site.custom', ['ru'=>'Общие']) ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'franchise.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('app', 'franchise.views.site.settings_three', ['ru'=>'Настройки']),
            Yii::t('app', 'franchise.views.site.common', ['ru'=>'Общие']),
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <?php
        Pjax::begin(['enablePushState' => false, 'id' => 'settingsInfo', 'timeout' => 5000]);
        $form = ActiveForm::begin([
                    'id' => 'generalSettings',
                    'enableAjaxValidation' => false,
                    'options' => [
                        'data-pjax' => true,
                    ],
                    'method' => 'post',
        ]);
        ?>
        <div class="box-body">
            <div class="row">

                <div class="col-md-12">
                    <h3><?= Yii::t('app', 'franchise.views.site.managers_settings', ['ru'=>'Настройки менеджера франшизы']) ?> <small><?= Yii::t('app', 'franchise.views.site.clients_helper', ['ru'=>'Помошник клиентов в регионе']) ?></small></h3>
                    <!--div class="row">
                        <div class="col-md-12">
                            <div class="avatar-option" style="">

                                <div class="upload-demo-wrap">
                                    <div id="upload-avatar"></div>
                                </div>
                                <img id="newAvatar" style="background-color:#ccc; display: block; width: 420px; margin-top: 15px; z-index: 1; max-height:236px;" class="center-block" src="<?= $franchisee->pictureUrl ?>">
                                <label class="btn btn-gray" id="uploadAvatar" style="width:420px; display: block; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px;"> Загрузить аватар
                                    <?=
                                            $form->field($franchisee, 'picture_manager', ['template' => '<div class="input-group">{input}</div>{error}'])
                                            ->fileInput(['id' => 'upload', 'accept' => 'image/*', 'style' => 'opacity: 0; z-index: -1;position: absolute;left: -9999px;'])
                                    ?>
                                </label>
                                <div id="stub" style="width:420px; display: none; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px; height: 44px; background-color: #3f3e3e;"></div>

                                <?= Html::hiddenInput('Franchisee[picture]', null, ['id' => 'image-crop-result']) ?>


                            </div>
                        </div>
                    </div-->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                    <?=
                                            $form->field($franchisee, 'fio_manager', [
                                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                            ])
                                            ->label(Yii::t('app', 'franchise.views.site.settings.manager', ['ru'=>'Менеджер']).' <span style="font-size:12px; color: #dd4b39;"></span>')
                                            ->textInput(['placeholder' => Yii::t('app', 'franchise.views.site.managers_fio', ['ru'=>'ФИО менеджера'])])
                                    ?>
                            </div>
                        </div>
                        <div class="col-md-4" style="width:auto">
                            <div class="form-group">
                                <?=
                                    $form->field($franchisee, 'phone_manager')
                                    ->widget(\common\widgets\phone\PhoneInput::className(), [
                                    'jsOptions' => [
                                        'preferredCountries' => ['ru'],
                                        'nationalMode' => false,
                                        'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                    ],
                                    'options' => [
                                        'class' => 'form-control',
                                    ],
                                ])
                                        ->label(Yii::t('app', 'franchise.views.site.managers_phone', ['ru'=>'Телефон менеджера']))
                                ?>                           
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                    <?=
                                            $form->field($franchisee, 'additional_number_manager')
                                            ->label(Yii::t('app', 'franchise.views.site.additional_number', ['ru'=>'Добавочный номер']))
                                            ->textInput(['placeholder' => ''])
                                    ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> ' . Yii::t('app', 'franchise.views.site.save_changes', ['ru'=>'Сохранить изменения']) . ' ', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> ' . Yii::t('app', 'franchise.views.site.cancel_changes', ['ru'=>'Отменить изменения']) . ' ', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</section>
