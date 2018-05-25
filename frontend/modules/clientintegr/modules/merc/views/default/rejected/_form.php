<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>
    <?php
    if($model->decision == \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentDonePartial::RETURN_ALL)
        echo $form->field($model, 'volume')->hiddenInput(['value' => 0])->label(false);
    else
        echo $form->field($model, 'volume')->textInput()->label($model->getAttributeLabel('volume')." (".$volume.")"); ?>

    <?php echo $form->field($model, 'reason')->textInput() ?>

    <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <div class="form-group">
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
