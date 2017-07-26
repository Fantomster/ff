<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>

    <?php echo $form->field($model, 'corr_rid')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'store_rid')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'doc_date')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>
    
    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => true]) ?>
    
    <?php // echo $form->field($model, 'num_code')->hiddenInput(['value' => Yii::$app->user->identity->userProfile->branch_id])->label(''); ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

