<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'Тестовая почта';
$this->params['breadcrumbs'][] = $this->title;
?>

<section class="content">
    <div class="box box-info">
        <div class="box-body">

            <?php if (Yii::$app->session->hasFlash('email-success')): ?>
                <div class="alert alert-success" role="alert">
                    <?= Yii::$app->session->getFlash('email-success') ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'email')->textInput() ?>
            <hr>

            <div class="form-group">
                <?= Html::submitButton('Send', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</section>