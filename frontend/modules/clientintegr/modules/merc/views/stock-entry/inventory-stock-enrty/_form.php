<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>
    <?php
        echo $form->field($model, 'volume')->textInput()->label($model->getAttributeLabel('volume')." (".$volume.")"); ?>

    <?php echo $form->field($model, 'reason')->textInput() ?>

    <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
