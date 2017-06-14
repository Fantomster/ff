<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model api\common\models\RkAccessSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rk-access-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'fid') ?>

    <?= $form->field($model, 'org') ?>

    <?= $form->field($model, 'login') ?>

    <?= $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'token') ?>

    <?php // echo $form->field($model, 'lic') ?>

    <?php // echo $form->field($model, 'fd') ?>

    <?php // echo $form->field($model, 'td') ?>

    <?php // echo $form->field($model, 'ver') ?>

    <?php // echo $form->field($model, 'locked') ?>

    <?php // echo $form->field($model, 'usereq') ?>

    <?php // echo $form->field($model, 'comment') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
