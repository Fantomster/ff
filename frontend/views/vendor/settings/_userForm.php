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
            'action' => $user->isNewRecord ? Url::toRoute('vendor/ajax-create-user') : Url::toRoute(['vendor/ajax-update-user', 'id' => $user->id]),
            'options' => [
                'class' => 'user-form',
            ],
            'validationUrl' => Url::toRoute('vendor/ajax-validate-user'),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= $user->isNewRecord ? Yii::t('message', 'frontend.views.vendor.new_user', ['ru'=>'Новый пользователь']) : Yii::t('message', 'frontend.views.vendor.edit_user', ['ru'=>'Редактировать пользователя']) ?></h4>
</div>
<div class="modal-body">
    <input type="email" name="fake_email" style="position: absolute; top: -100%;">
    <input type="password" name="fake_pwd" style="position: absolute; top: -100%;">

    <?= $form->field($user, 'email') ?>

    <?= $form->field($user, 'newPassword')->passwordInput() ?>

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

    <?= $form->field($user, 'role_id')->dropDownList(Role::dropdown($organizationType))->label(Yii::t('message', 'frontend.views.vendor.role', ['ru'=>"Роль"])) ?>

</div>
<div class="modal-footer">
    <?=
    Html::button($user->isNewRecord ? '<i class="icon fa fa-user-plus"></i> ' . Yii::t('message', 'frontend.views.vendor.create_three', ['ru'=>'Создать']) . ' ' : '<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.vendor.save_eight', ['ru'=>'Сохранить']) . ' ', [
        'class' => 'btn btn-success edit',
        'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.vendor.saving_two', ['ru'=>'Сохраняем...'])])
    ?>
<?=
Html::button('<i class="fa fa-fw fa-trash-o"></i> ' . Yii::t('message', 'frontend.views.vendor.del_five', ['ru'=>'Удалить']) . ' ', [
    'class' => 'btn btn-danger delete',
    'data' => [
        'id' => $user->id,
        'action' => Url::to(["vendor/ajax-delete-user"]),
        'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.vendor.deleting', ['ru'=>'Удаляем...'])
]])
?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('message', 'frontend.views.vendor.cancel_fourteen', ['ru'=>'Отмена']) ?></a>
</div>
<?php ActiveForm::end(); ?>
