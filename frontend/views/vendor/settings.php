<?php

/* @var $this \yii\web\View */


use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
\common\assets\GoogleMapsAsset::register($this);

$this->title = Yii::t('message', 'frontend.views.vendor.settings', ['ru' => 'Настройки']);
$this->registerJs(
    '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                //$.pjax.reload({container: "#settingsInfo"});      
                location.reload();
            });
            $(".settings").on("change paste keyup", ".form-control, input", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
            $(".settings").on("click", ".country, #map", function() {
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
//if ($organization->step == common\models\Organization::STEP_SET_INFO) {
//    echo yii\bootstrap\Alert::widget([
//        'options' => [
//            'class' => 'alert-warning fade in',
//        ],
//        'body' => Yii::t('message', 'frontend.views.vendor.continue', ['ru'=>'Далее'])
//        . '<a class="btn btn-default btn-sm" href="#">' . Yii::t('message', 'frontend.views.vendor.do_it', ['ru'=>'Сделаем это!']) . ' </a>',
//    ]);
//}
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.vendor.custom', ['ru' => 'Общие']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.org_info', ['ru' => 'Информация об организации']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.set', ['ru' => 'Настройки']),
            Yii::t('message', 'frontend.views.vendor.cust', ['ru' => 'Общие']),
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <?php
        //Pjax::begin(['enablePushState' => false, 'id' => 'settingsInfo', 'timeout' => 5000]);
        $form = ActiveForm::begin([
            'id' => 'generalSettings',
            'enableAjaxValidation' => false,
//                    'options' => [
//                        'data-pjax' => true,
//                    ],
            'method' => 'post',
        ]);
        ?>
        <div class="box-body">
            <div class="row">

                <div class="col-md-12">
                    <fieldset>
                        <legend><?= Yii::t('message', 'frontend.views.vendor.org_data', ['ru' => 'Данные организации:']) ?></legend>
                        <div class="avatar-option" style="">

                            <div class="upload-demo-wrap">
                                <div id="upload-avatar"></div>
                            </div>
                            <img id="newAvatar"
                                 style="background-color:#ccc; display: block; width: 420px; margin-top: 15px; z-index: 1; max-height:236px; height:236px;"
                                 class="center-block" src="<?= $organization->pictureUrl ?>">
                            <label class="btn btn-gray" id="uploadAvatar"
                                   style="width:420px; display: block; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px;"> <?= Yii::t('message', 'frontend.views.vendor.avatar', ['ru' => 'Загрузить аватар']) ?>
                                <?=
                                $form->field($organization, 'picture', ['template' => '<div class="input-group">{input}</div>{error}'])
                                    ->fileInput(['id' => 'upload', 'accept' => 'image/*', 'style' => 'opacity: 0; z-index: -1;position: absolute;left: -9999px;'])
                                ?>
                            </label>
                            <div id="stub"
                                 style="width:420px; display: none; margin: 0 auto; z-index: 999; border-radius: 0; margin-bottom:20px; height: 44px; background-color: #3f3e3e;"></div>

                            <?= Html::hiddenInput('Organization[picture]', null, ['id' => 'image-crop-result']) ?>


                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'name', [
                                    'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                ])
                                    ->label(Yii::t('message', 'frontend.views.vendor.vendor_name', ['ru' => 'Название поставщика']) . '  <span style="font-size:12px; color: #dd4b39;"></span>')
                                    ->textInput(['value' => Html::decode($organization->name), 'placeholder' => Yii::t('message', 'frontend.views.vendor.name_insert', ['ru' => 'Введите название поставщика'])])
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'legal_entity', [
                                    'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                ])
                                    ->label(Yii::t('message', 'frontend.views.vendor.jur_name', ['ru' => 'Название юридического лица']) . '  <span style="font-size:12px; color: #dd4b39;"></span>')
                                    ->textInput(['value' => Html::decode($organization->legal_entity), 'placeholder' => Yii::t('message', 'frontend.views.vendor.jur_name_insert', ['ru' => 'Введите название юридического лица'])])
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'website', [
                                    'addon' => ['prepend' => ['content' => '<i class="fa fa-globe"></i>']]
                                ])
                                    ->label(Yii::t('message', 'frontend.views.vendor.site', ['ru' => 'Веб-сайт']))
                                    ->textInput(['value' => Html::decode($organization->website), 'placeholder' => Yii::t('message', 'frontend.views.vendor.site_print', ['ru' => 'Введите адрес вашего веб-сайта'])])
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'about')
                                    ->label(Yii::t('message', 'frontend.views.vendor.org_info_two', ['ru' => 'Информация об организации']))
                                    ->textarea(['value' => Html::decode($organization->about), 'placeholder' => Yii::t('error', 'frontend.views.vendor.several_words', ['ru' => "Несколько слов об организации ..."]), 'rows' => 2])
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'is_allowed_for_franchisee')->checkbox()
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                $form->field($organization, 'address', [
                                    'addon' => ['prepend' => ['content' => '<i class="fa fa-compass"></i>']]
                                ])
                                    ->label(Yii::t('message', 'frontend.views.vendor.add', ['ru' => 'Адрес']))
                                    ->textInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.enter_add', ['ru' => 'Введите ваш адрес'])])
                                ?>
                            </div>
                            <div id="map" style="width:100%;height:250px;"></div>
                            <?= Html::activeHiddenInput($organization, 'lat'); //широта  ?>
                            <?= Html::activeHiddenInput($organization, 'lng'); //долгота  ?>
                            <?= Html::activeHiddenInput($organization, 'country'); //страна  ?>
                            <?= Html::activeHiddenInput($organization, 'locality'); //Город  ?>
                            <?= Html::activeHiddenInput($organization, 'administrative_area_level_1'); //область  ?>
                            <?= Html::activeHiddenInput($organization, 'route'); //улица  ?>
                            <?= Html::activeHiddenInput($organization, 'street_number'); //дом  ?>
                            <?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места  ?>
                            <?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес  ?>
                            <script type="text/javascript">

                                function stopRKey(evt) {
                                    var evt = (evt) ? evt : ((event) ? event : null);
                                    var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
                                    if ((evt.keyCode == 13) && (node.type == "text")) {
                                        return false;
                                    }
                                }

                                document.onkeypress = stopRKey;

                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <fieldset>
                <legend><?= Yii::t('message', 'frontend.views.vendor.contact', ['ru' => 'Контактное лицо:']) ?></legend>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                            $form->field($organization, 'contact_name', [
                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                            ])
                                ->label(Yii::t('message', 'frontend.views.vendor.fio', ['ru' => 'ФИО контактного лица']))
                                ->textInput(['value' => Html::decode($organization->contact_name), 'placeholder' => Yii::t('message', 'frontend.views.vendor.cont_fio', ['ru' => 'Введите ФИО контактного лица'])])
                            ?>                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                            $form->field($organization, 'email', [
                                'addon' => ['prepend' => ['content' => '<i class="fa fa-envelope"></i>']]
                            ])
                                ->label('E-mail')
                                ->textInput(['placeholder' => Yii::t('message', 'frontend.views.vendor.enter_email', ['ru' => "Введите E-mail"])])
                            ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <?=
                            $form->field($organization, 'phone')
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
                                ->label(Yii::t('message', 'frontend.views.vendor.phone', ['ru' => 'Телефон']))
                            ?>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save', ['ru' => 'Сохранить изменения']) . ' ', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> ' . Yii::t('message', 'frontend.views.vendor.', ['ru' => 'Отменить изменения']) . ' ', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        //Pjax::end();
        ?>
    </div>
</section>
