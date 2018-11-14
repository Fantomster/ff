<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="production-act-defect-form">


    <?php $form = ActiveForm::begin(); ?>
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

    <div class="form-group">
        <?= Html::submitButton(Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
