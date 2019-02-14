<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PromoAction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="promo-action-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>


    <?= $form->field($model, 'message')->widget(
        \yii\imperavi\Widget::class,
        [
            'plugins' => ['fontsize', 'fullscreen', 'fontcolor', 'fontfamily', 'limiter', 'table'],
            'options' => [
                'minHeight'       => 400,
                'maxHeight'       => 400,
                'buttonSource'    => true,
                'convertDivs'     => false,
                'removeEmptyTags' => true,
                'limiter'         => 1000,
            ],
        ]
    ) ?>
    <p>
        Допустимые макросы:
    <ul>
        <li>:lead_name|"текст замены если пусто"</li>
        <li>:lead_city|"текст замены если пусто"</li>
    </ul>
    </p>


    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
