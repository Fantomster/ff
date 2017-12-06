<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
use kartik\checkbox\CheckboxX;

kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
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
    <input type="email" name="fake_email" style="position: absolute; top: -100%;">
    <input type="password" name="fake_pwd" style="position: absolute; top: -100%;">

    <?= $form->field($user, 'email') ?>

    <?= $form->field($user, 'newPassword')->label(Yii::t('app', 'frontend.views.user_form.new_pass', ['ru'=>'Новый пароль']))->passwordInput() ?>

    <?= $form->field($profile, 'full_name') ?>

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

    <?= $form->field($user, 'role_id')->dropDownList(Role::dropdown($organizationType))->label(Yii::t('message', 'frontend.views.client.settings.role', ['ru'=>"Роль"])) ?>

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
