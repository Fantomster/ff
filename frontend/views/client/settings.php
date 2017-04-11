<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
$this->title = 'Настройки';
$this->registerJs(
        '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                $.pjax.reload({container: "#settingsInfo"});      
            });
            $(".settings").on("change paste keyup", ".form-control, input", function() {
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
<!--<div style="padding: 20px 30px; background: rgb(243, 156, 18); z-index: 999999; font-size: 16px; font-weight: 600;"><a class="pull-right" href="#" data-toggle="tooltip" data-placement="left" title="Never show me this again!" style="color: rgb(255, 255, 255); font-size: 20px;">×</a><a href="https://themequarry.com" style="color: rgba(255, 255, 255, 0.901961); display: inline-block; margin-right: 10px; text-decoration: none;">Ready to sell your theme? Submit your theme to our new marketplace now and let over 200k visitors see it!</a><a class="btn btn-default btn-sm" href="https://themequarry.com" style="margin-top: -5px; border: 0px; box-shadow: none; color: rgb(243, 156, 18); font-weight: 600; background: rgb(255, 255, 255);">Let's Do It!</a></div>-->
<?php
if ($organization->step == common\models\Organization::STEP_SET_INFO) {
    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-warning fade in',
        ],
        'body' => 'Для того, чтобы продолжить работу с нашей системой, заполните все необходимые поля формы. '
        . '<a class="btn btn-default btn-sm" href="#">Сделаем это!</a>',
    ]);
}
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Общие
        <small>Информация об организации</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Общие',
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
                    <fieldset>
                        <legend>Данные организации:</legend>
                        <div class="avatar-option" style="">

                            <div class="upload-demo-wrap">
                                <div id="upload-avatar"></div>
                            </div>
                            <img id="newAvatar" style="background-color:#ccc; display: block; width: 420px; margin-top: 15px; z-index: 1; max-height:236px;" class="center-block" src="<?= $organization->pictureUrl ?>">
                            <label class="btn btn-gray" id="uploadAvatar" style="width:420px; display: block; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px;"> Загрузить аватар
                                <?=
                                        $form->field($organization, 'picture', ['template' => '<div class="input-group">{input}</div>{error}'])
                                        ->fileInput(['id' => 'upload', 'accept' => 'image/*', 'style' => 'opacity: 0; z-index: -1;position: absolute;left: -9999px;'])
                                ?>
                            </label>
                            <div id="stub" style="width:420px; display: none; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px; height: 44px; background-color: #3f3e3e;"></div>

                            <?= Html::hiddenInput('Organization[picture]', null, ['id' => 'image-crop-result']) ?>


                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <?=
                                            $form->field($organization, 'name', [
                                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                            ])
                                            ->label('Название ресторана <span style="font-size:12px; color: #dd4b39;"></span>')
                                            ->textInput(['placeholder' => 'Введите название ресторана'])
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <?=
                                            $form->field($organization, 'legal_entity', [
                                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                            ])
                                            ->label('Название юридического лица <span style="font-size:12px; color: #dd4b39;"></span>')
                                            ->textInput(['placeholder' => 'Введите название юридического лица'])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'city', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-map"></i>']]
                                        ])
                                        ->label('Город')
                                        ->textInput(['placeholder' => 'Введите ваш город'])
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'address', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-compass"></i>']]
                                        ])
                                        ->label('Адрес')
                                        ->textInput(['placeholder' => 'Введите ваш адрес'])
                                ?>                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'website', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-globe"></i>']]
                                        ])
                                        ->label('Веб-сайт')
                                        ->textInput(['placeholder' => 'Введите адрес вашего веб-сайта'])
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?=
                                $form->field($organization, 'about')
                                ->label('Информация об организации')
                                ->textarea(['placeholder' => "Несколько слов об организации ...", 'rows' => 3])
                        ?>
                    </div>
                </div>
            </div>
            <fieldset>
                <legend>Контактное лицо:</legend>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                                    $form->field($organization, 'contact_name', [
                                        'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                    ])
                                    ->label('ФИО контактного лица')
                                    ->textInput(['placeholder' => 'Введите ФИО контактного лица'])
                            ?>                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                                    $form->field($organization, 'email', [
                                        'addon' => ['prepend' => ['content' => '<i class="fa fa-envelope"></i>']]
                                    ])
                                    ->label('E-mail')
                                    ->textInput(['placeholder' => "Введите E-mail"])
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                                    $form->field($organization, 'phone', [
                                        'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                                    ])
                                    ->widget(\common\widgets\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ])
                                    ->label('Телефон')
                                    ->textInput()
                            ?>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</section>
