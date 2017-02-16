<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\FranchiseeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="franchisee-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'signed') ?>

    <?= $form->field($model, 'legal_entity') ?>

    <?= $form->field($model, 'legal_address') ?>

    <?= $form->field($model, 'legal_email') ?>

    <?php // echo $form->field($model, 'inn') ?>

    <?php // echo $form->field($model, 'kpp') ?>

    <?php // echo $form->field($model, 'ogrn') ?>

    <?php // echo $form->field($model, 'bank_name') ?>

    <?php // echo $form->field($model, 'bik') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'correspondent_account') ?>

    <?php // echo $form->field($model, 'checking_account') ?>

    <?php // echo $form->field($model, 'info') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
