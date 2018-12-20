<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
use kartik\checkbox\CheckboxX;

kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
\frontend\assets\HandsOnTableAsset::register($this);
?>
<?php \yii\widgets\Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'timeout' => 10000, 'id' => 'sp-list']) ?>
<?php
$form = ActiveForm::begin([
            'id' => 'user-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
            'action' => $user->isNewRecord ? Url::toRoute('client/ajax-create-user') : Url::toRoute(['client/ajax-update-user', 'id' => $user->id]),
            'options' => [
                'class' => 'user-form',
            ],
            'validationUrl' => Url::toRoute('client/ajax-validate-user'),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= $user->isNewRecord ? Yii::t('message', 'frontend.views.client.settings.new', ['ru'=>'Новый пользователь']) : Yii::t('message', 'frontend.views.client.settings.edit', ['ru'=>'Редактировать пользователя']) ?></h4>
</div>
<div class="modal-body">
    <input type="email"  name="Userito[email]" style="position: absolute; top: -100%;">
    <input type="password" name="new-password" style="position: absolute; top: -100%;">

    <?= $form->field($user, 'email')->textInput(['disabled' => $user->isNewRecord ? false : true]) ?>

    <?= $form->field($user, 'newPassword')->label(Yii::t('app', 'frontend.views.user_form.new_pass', ['ru'=>'Новый пароль']))->passwordInput() ?>

    <?= $form->field($profile, 'full_name')->textInput(['value' => Html::decode($profile->full_name), ]) ?>

    <?=
    $form->field($profile, 'phone')->widget(\common\widgets\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode' => false,
            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
        ],
        'options' => [
            'class' => 'form-control',
        ],
    ])
    ?>

    <?= $form->field($user, 'role_id')->dropDownList($dropDown, ['options' =>[ $selected => ['Selected' => true]]])->label(Yii::t('message', 'frontend.views.client.settings.role', ['ru'=>"Роль"])) ?>

</div>
<div class="modal-footer">
    <?=
    Html::button($user->isNewRecord ? '<i class="icon fa fa-user-plus"></i> ' . Yii::t('message', 'frontend.views.client.settings.create', ['ru'=>'Создать']) : '<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.client.settings.save_two', ['ru'=>'Сохранить']), [
        'class' => 'btn btn-success edit',
        'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.client.settings.saving', ['ru'=>'Сохраняем...'])])
    ?>
    <?=
    Html::button('<i class="fa fa-fw fa-trash-o"></i> ' . Yii::t('message', 'frontend.views.client.settings.del', ['ru'=>'Удалить']), [
        'class' => 'btn btn-danger delete',
        'data' => [
            'id' => $user->id,
            'action' => Url::to(["client/ajax-delete-user"]),
            'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.client.settings.deleting', ['ru'=>'Удаляем...'])
]])
    ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('message', 'frontend.views.client.settings.cancel_two', ['ru'=>'Отмена']) ?></a>
</div>
<?php ActiveForm::end(); ?>
<?php \yii\widgets\Pjax::end(); ?>


<?php
$chkmailUrl = Url::to(['client/check-email']);

$customJs = <<< JS

$('#user-form').on('afterValidateAttribute', function (event, attribute, messages) {	
	var hasError = messages.length !==0;
    var field = $(attribute.container);
    var input = field.find(attribute.input);
	input.attr("aria-invalid", hasError ? "true" : "false");
    if (attribute.name === 'email' && !hasError)
        {
            $.ajax({
            url: "$chkmailUrl",
            type: "POST",
            dataType: "json",
            data: {'email' : input.val()},
            success: function(response) {
                if(response.success){
	                if(response.eventType==6){
		                var fio = response.fio;
                        var phone = response.phone; 
                        var organization = response.organization;
	                    $('#profile-full_name').val(fio);
                        $('#profile-phone').val(phone);
                        $('#organization-name').val(organization);
                        $('#user-newpassword').val('********');
                        $('#profile-full_name,#profile-phone,#organization-name,#user-newpassword').attr('readonly','readonly');
		                console.log('type = 6');    
	                }else{
	                    $('#profile-full_name,#profile-phone,#organization-name,#user-newpassword').removeAttr('readonly');
	                }            
                } else {
		            $('#profile-full_name,#profile-phone,#organization-name,#user-newpassword').removeAttr('readonly');
                }
            },
            error: function(response) {
		        console.log(response.message); 
            }
        }); 
	}	 
});

JS;

$this->registerJs($customJs, \yii\web\View::POS_READY);
