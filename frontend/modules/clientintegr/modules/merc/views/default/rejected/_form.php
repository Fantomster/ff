<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(); ?>

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

    <div class="form-group">
        <?php
        if($model->mode == \frontend\modules\clientintegr\modules\merc\models\rejectedForm::INPUT_MODE) {
            echo Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success']);
        }
        else
        {
            echo Html::submitButton(Yii::t('message', 'market.views.site.main.confirm', ['ru' => 'Подтвердить']), ['class' => 'btn btn-success']);
        }
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
