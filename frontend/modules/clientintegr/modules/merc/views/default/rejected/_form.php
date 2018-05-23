<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\TouchSpin;
use kartik\widgets\DatePicker;
use kartik\widgets\SwitchInput;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'uuid')->hiddenInput(['value' => $model->uuid])->label(false); ?>

    <?php echo $form->field($model, 'volume')->textInput() ?>

    <?php echo $form->field($model, 'reason')->textInput() ?>

    <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <div class="form-group">
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
