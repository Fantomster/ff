<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BusinessInfo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="buisiness-info-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'signed')->textInput() ?>
    
    <?= $form->field($model, 'legal_entity')->textInput() ?>
    
    <?= $form->field($model, 'legal_address')->textInput() ?>
    
    <?= $form->field($model, 'legal_email')->textInput() ?>
    
    <?= $form->field($model, 'inn')->textInput() ?>
    
    <?= $form->field($model, 'kpp')->textInput() ?>
    
    <?= $form->field($model, 'ogrn')->textInput() ?>
    
    <?= $form->field($model, 'bank_name')->textInput() ?>
    
    <?= $form->field($model, 'bik')->textInput() ?>
    
    <?= $form->field($model, 'correspondent_account')->textInput() ?>
    
    <?= $form->field($model, 'checking_account')->textInput() ?>
    
    <?= $form->field($model, 'phone')->textInput() ?>
    
    <?= $form->field($model, 'info')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
