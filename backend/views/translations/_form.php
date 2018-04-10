<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="franchisee-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($sourceMessage, 'category')->textInput(['maxlength' => true, 'value'=>'app'])->label('Категория') ?>

    <?= $form->field($sourceMessage, 'message')->textInput(['maxlength' => true])->label('Переменная') ?>

    <?= $form->field($message, 'translation[ru]')->textInput(['maxlength' => true])->label('Перевод[ru]') ?>

    <?= $form->field($message, 'translation[en]')->textInput(['maxlength' => true])->label('Перевод[en]') ?>

    <?= $form->field($message, 'translation[es]')->textInput(['maxlength' => true])->label('Перевод[es]') ?>

    <?= $form->field($message, 'translation[md]')->textInput(['maxlength' => true])->label('Перевод[md]') ?>

    <div class="form-group">
        <?= Html::submitButton('Create') ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
