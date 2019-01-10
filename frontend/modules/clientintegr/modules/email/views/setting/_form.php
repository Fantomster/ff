<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\IntegrationSettingFromEmail */
/* @var $form ActiveForm */
/* @var $user \common\models\User */

?>
<style>
    .hint-block {
        font-size: 10px;
        color: grey;
    }
</style>
<div class="form col-sm-6">
    <?php $form = ActiveForm::begin(); ?>
    <?= Html::hiddenInput('IntegrationSettingFromEmail[id]', ($model->id ?? null)) ?>
    <?= Html::hiddenInput('IntegrationSettingFromEmail[organization_id]', $user->organization_id) ?>
    <?= $form->field($model, 'server_type')->dropDownList(['imap' => 'IMAP']) ?>
    <?= $form->field($model, 'server_host')->hint('Пример: imap.yandex.ru') ?>
    <?= $form->field($model, 'server_port')->hint('IMAP: 993')->textInput(['type' => 'number']) ?>
    <?= $form->field($model, 'user')->hint('Логин от почты') ?>
    <?= $form->field($model, 'password')->passwordInput([
        'value' => $model->countCharsPassword
    ])->hint('Пароль от почты') ?>
    <?= $form->field($model, 'language')->dropDownList(['ru' => 'ru (Русский)', 'en' => 'en (Английский)', 'es' => 'es (Испанский)', 'md' => 'md (Молдавский)', 'ua' => 'ua (Украинский)']) ?>
    <?= $form->field($model, 'server_ssl')->dropDownList(['1' => 'Да', '0' => 'Нет']) ?>
    <?= $form->field($model, 'is_active')->dropDownList(['1' => 'Да', '0' => 'Нет']) ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>