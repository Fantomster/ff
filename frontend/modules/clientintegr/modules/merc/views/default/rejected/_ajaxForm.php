<?php

use yii\helpers\Html;
use common\themes\dasha\widgets\yii2\ActiveForm;
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
        <h4 class="modal-title">Акт несоответствия</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <?php echo $form->errorSummary($model); ?>

        <?php echo $form->field($model, 'uuid')->hiddenInput(['value' => $model->uuid])->label(false); ?>

        <?php echo $form->field($model, 'volume')->textInput() ?>

        <?php echo $form->field($model, 'reason')->textInput() ?>

        <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
    </div>
    <div class="modal-footer">
        <?= Html::button('<i class="icon fa fa-save"></i> Сохранить ', ['class' => 'btn btn-success save-form']) ?>
        <a href="#" class="btn btn-info" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'Close') ?></a>
    </div>
<?php ActiveForm::end(); ?>