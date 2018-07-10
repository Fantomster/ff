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
        <h4 class="modal-title"><?= Yii::t('message', 'frontend.client.integration.mercury.act', ['ru'=>'Акт несоответствия']); ?></h4>
    </div>
    <div class="modal-body">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <h4>
                    <i class="icon fa fa-check"></i><?= Yii::t('message', 'frontend.client.integration.mercury.successful', ['ru' => 'Выполнено']) ?>
                </h4>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <h4>
                    <i class="icon fa fa-exclamation-circle"></i><?= Yii::t('message', 'frontend.views.vendor.error', ['ru' => 'Ошибка']) ?>
                </h4>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
        <?php echo $form->errorSummary($model); ?>
        <?php
        if($model->decision ==  \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone::RETURN_ALL)
            echo $form->field($model, 'volume')->hiddenInput(['value' => 0])->label(false);
        else
            echo $form->field($model, 'volume')->textInput()->label($model->getAttributeLabel('volume')." (".$volume.")"); ?>

        <?php echo $form->field($model, 'reason')->textInput() ?>

        <?php echo $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
    </div>
    <div class="modal-footer">
        <?= Html::button('<i class="icon fa fa-save save-form"></i> '.Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success save-form']) ?>
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'Close') ?></a>
    </div>
<?php ActiveForm::end(); ?>