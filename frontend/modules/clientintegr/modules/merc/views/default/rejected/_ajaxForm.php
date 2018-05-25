<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\TouchSpin;
use kartik\widgets\DatePicker;
use kartik\widgets\SwitchInput;

?>
<?php
$form = ActiveForm::begin([
    'id' => 'ajax-form',
    'enableAjaxValidation' => false,
]);
?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Акт несоответствия</h4>
    </div>
    <div class="modal-body">
        <?php echo $form->errorSummary($model); ?>
        <?php
        if($model->decision == \frontend\modules\clientintegr\modules\merc\helpers\vetDocumentDonePartial::RETURN_ALL)
            echo $form->field($model, 'volume')->hiddenInput(['value' => 0])->label(false);
        else
            echo $form->field($model, 'volume')->textInput()->label($model->getAttributeLabel('volume')." (".$volume.")"); ?>

        <?php echo $form->field($model, 'reason')->textInput() ?>

        <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
    </div>
    <div class="modal-footer">
        <?= Html::button('<i class="icon fa fa-save save-form"></i> Сохранить ', ['class' => 'btn btn-success save-form']) ?>
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'Close') ?></a>
    </div>
<?php ActiveForm::end(); ?>