<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; ?>
    <?php $form = ActiveForm::begin(); ?>
    <?php echo $form->errorSummary($model); ?>
    <?php
    switch ($dicConst->type) {
        case \api\common\models\one_s\OneSDicconst::TYPE_DROP :
            if ($dicConst->denom === 'taxVat') {
                echo $form->field($model, 'value')->dropDownList([
                    '0'    => '0',
                    '1000' => '10',
                    '1800' => '18',
                    '2000' => '20'
                ]);
            } elseif ($dicConst->denom === 'auto_unload_invoice') {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                    '2' => 'Полуавтомат',
                ]);
            } else {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                ]);
            }
            break;
        case \api\common\models\one_s\OneSDicconst::TYPE_PASSWORD:
            echo $form->field($model, 'value')->passwordInput(['maxlength' => true]);
            break;
        default:
            echo $form->field($model, 'value')->textInput(['maxlength' => true]);
    }
    ?>
    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Вернуться', ['index'], ['class' => 'btn btn-success btn-export']); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

