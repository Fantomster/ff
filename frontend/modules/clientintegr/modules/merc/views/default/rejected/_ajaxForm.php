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
        echo $form->field($model, 'mode')->hiddenInput()->label(false);
        if($model->mode == \frontend\modules\clientintegr\modules\merc\models\rejectedForm::INPUT_MODE) {
            if ($model->decision == \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone::RETURN_ALL) {
                echo $form->field($model, 'volume')->hiddenInput(['value' => 0])->label(false);
            }
            else {
                echo $form->field($model, 'volume')->textInput()->label($model->getAttributeLabel('volume') . " (" . $volume . ")");
            }
            echo $form->field($model, 'reason')->textInput();
            echo $form->field($model, 'description')->textarea(['maxlength' => true]);
            }
        else {
            echo $form->field($model, 'volume')->hiddenInput()->label(false);
            echo $form->field($model, 'reason')->hiddenInput()->label(false);
            echo $form->field($model, 'description')->hiddenInput()->label(false);
            echo $form->field($model, 'conditions')->hiddenInput()->label(false);
            echo "<h4>Подтвердите выполнение условий регионализации: </h4>";
            $conditions = json_decode($model->conditions, true);
            echo " <div style=\"padding-left: 15px;padding-right: 15px;\"><ul>";
            foreach ($conditions as $item) {
                echo "<li style=\"padding-bottom: 10px;\">".$item."</li>";
            }
            echo "</ul></div>";
        }?>
    </div>
    <div class="modal-footer">
        <?php
        if($model->mode == \frontend\modules\clientintegr\modules\merc\models\rejectedForm::INPUT_MODE) {
            echo Html::button('<i class="icon fa fa-save save-form"></i> '.Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success save-form']);
        }
        else
        {
            echo Html::button('<i class="icon fa fa-save save-form"></i> '.Yii::t('message', 'market.views.site.main.confirm', ['ru' => 'Подтвердить']), ['class' => 'btn btn-success save-form']);
        }
        ?>
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'Close') ?></a>
    </div>
<?php ActiveForm::end(); ?>