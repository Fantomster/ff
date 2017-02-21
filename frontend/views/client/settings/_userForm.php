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
            'action' => $user->isNewRecord? Url::toRoute('client/ajax-create-user') : Url::toRoute(['client/ajax-update-user', 'id' => $user->id]),
            'options' => [
                'class' => 'user-form',
            ],
            'validationUrl' => Url::toRoute('client/ajax-validate-user'),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= $user->isNewRecord? 'Новый пользователь' : 'Редактировать пользователя' ?></h4>
</div>
<div class="modal-body">

    <?= $form->field($user, 'email') ?>

    <?= $form->field($user, 'newPassword')->passwordInput() ?>

    <?= $form->field($profile, 'full_name') ?>

    <?= $form->field($profile, 'phone')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',]) ?>
    
    <?=$form->field($profile, 'sms_allow')->widget(CheckboxX::classname(), [
                            //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                            'autoLabel' => true,
                            'model' => $profile,
                            'attribute' => 'sms_allow',
                            'pluginOptions'=>[
                                'threeState'=>false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => false,
                                'size'=>'md',
                                ],
                            'labelSettings' => [
                                'label' => 'Разрешить СМС уведомление',
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' =>['style'=>'']
                                ]
                            ])->label(false);?>

    <?= $form->field($user, 'role_id')->dropDownList(Role::dropdown($organizationType)) ?>

</div>
<div class="modal-footer">
    <?= Html::button($user->isNewRecord ? '<i class="icon fa fa-user-plus"></i> Создать' : '<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success edit']) ?>
    <?= Html::button('<i class="fa fa-fw fa-trash-o"></i> Удалить', [
        'class' => 'btn btn-danger delete', 
        'data' => [
            'id' => $user->id,
            'action' => Url::to(["client/ajax-delete-user"]),
        ]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Отмена</a>
</div>
<?php ActiveForm::end(); ?>
