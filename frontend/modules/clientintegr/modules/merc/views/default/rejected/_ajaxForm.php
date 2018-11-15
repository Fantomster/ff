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
            echo $form->field($model, 'conditionsDescription')->hiddenInput()->label(false);
            $style= <<< CSS
   .checkbox {
    margin-top: 0px;
    margin-bottom: 0px;
}
CSS;
            $this->registerCss($style);

            echo "<h4>Подтвердите выполнение условий регионализации: </h4>";
            $conditions = json_decode($model->conditionsDescription, true);
            /* echo "<pre>";
             var_dump($conditions); die();*/
            $step = 0;
            echo " <div style=\"padding-bottom: 15px;\">";
            foreach ($conditions as $key => $block) {
                if($step > 0) {
                    echo "<p><b>и</b></p>";
                }
                echo "<div style=\"-webkit-border-radius: 3px;border-radius: 3px;padding: 10px;border: 1px solid #ddd;\">";
                echo "<p><b><u>".$key."</u></b></p>";
                $i = 0;
                foreach ($block as $item) {
                    if($i > 0) {
                        echo "<p><b>или</b></p>";
                    }
                    echo $form->field($model, 'conditions[]',['template' => '{input}{error}'])
                        ->checkbox([
                            'label' => $item['text'],
                            'labelOptions' => [
                                'style' => 'padding-left:20px;'
                            ],
                            'value' => $item['guid']
                        ]);
                    $i++;
                }
                echo "</div>";
                $step++;
            }
            echo "</div>";
        }?>
    </div>
    <div class="modal-footer">
        <?php echo Html::button('<i class="icon fa fa-save save-form"></i> '.Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success save-form']); ?>
        <?php
        if($model->mode == \frontend\modules\clientintegr\modules\merc\models\rejectedForm::CONFIRM_MODE) {
            echo Html::button(Yii::t('message', 'frontend.views.layouts.client.integration.check_all', ['ru' => 'Выделить все']), ['class' => 'btn btn-primary', 'id'=>"select_all_conditions"]);
        }
        ?>
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'Close') ?></a>
    </div>
<?php ActiveForm::end(); ?>
<?php
$customJs = <<< JS
    $("#select_all_conditions").click( function()
          {
              $('#ajax-form input[type="checkbox"]').prop('checked', true);
           }
        );
JS;
$this->registerJs($customJs, \yii\web\View::POS_END);
?>
