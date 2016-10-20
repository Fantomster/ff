<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'user-form',
            'enableAjaxValidation' => false,
            'action' => $user->isNewRecord? Url::toRoute('vendor/ajax-create-user') : Url::toRoute(['vendor/ajax-update-user', 'id' => $user->id]),
            'options' => [
                'class' => 'user-form',
            ],
            'validationUrl' => Url::toRoute('vendor/ajax-validate-user'),
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

    <?= $form->field($user, 'role_id')->dropDownList(Role::dropdown($organizationType)) ?>

</div>
<div class="modal-footer">
    <?= Html::button($user->isNewRecord ? 'Создать' : 'Сохранить', ['class' => 'btn btn-success edit']) ?>
    <a href="#" class="btn btn-danger" data-dismiss="modal">Отмена</a>
</div>
<?php ActiveForm::end(); ?>
